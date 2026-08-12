<?php

namespace App\Services;

use App\Accounting\AccountCode;
use App\Enums\JournalEntryStatus;
use App\Enums\JournalEntryType;
use App\Enums\PaymentMethodType;
use App\Models\AccountingAccount;
use App\Models\CreditNote;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\User;
use App\Support\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Tenue du journal comptable (partie double, norme SCF).
 *
 * Chaque écriture est équilibrée (total débits = total crédits) et peut être :
 * - générée automatiquement depuis la facturation (émission facture,
 *   encaissement, avoir, remboursement, annulation) ;
 * - saisie manuellement par le comptable.
 */
class JournalEntryService
{
    public function __construct(
        private readonly SettingsService $settings,
    ) {}

    /**
     * Génère un numéro d'écriture unique (ECR-2026-000001).
     */
    public function generateNumber(): string
    {
        $prefix = $this->settings->get('journal_prefix', 'ECR');
        $year = now()->format('Y');
        $sequence = JournalEntry::withTrashed()->count() + 1;

        return "{$prefix}-{$year}-".str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Saisit une écriture équilibrée et la marque comme validée.
     *
     * @param  array<int, array{accounting_account_id?: int, account_code?: string, debit?: mixed, credit?: mixed, notes?: ?string}>  $lines
     */
    public function post(
        string $type,
        string $description,
        array $lines,
        ?string $entryDate = null,
        ?Model $source = null,
        ?User $actor = null,
    ): JournalEntry {
        $actor ??= auth()->user();

        return DB::transaction(function () use ($type, $description, $lines, $entryDate, $source, $actor): JournalEntry {
            $normalized = $this->normalizeLines($lines);
            $this->assertBalanced($normalized);

            $entry = JournalEntry::create([
                'entry_number' => $this->generateNumber(),
                'entry_date' => $entryDate ?? now()->toDateString(),
                'type' => $type,
                'description' => $description,
                'source_type' => $source ? $source->getMorphClass() : null,
                'source_id' => $source?->getKey(),
                'status' => JournalEntryStatus::Posted,
                'posted_at' => now(),
                'created_by' => $actor?->id,
            ]);

            $entry->lines()->createMany($normalized);

            activity('journal')
                ->performedOn($entry)
                ->causedBy($actor)
                ->withProperties([
                    'entry_number' => $entry->entry_number,
                    'type' => $type,
                    'description' => $description,
                ])
                ->log('Écriture comptable saisie');

            return $entry->load('lines.account');
        });
    }

    /**
     * Crée un brouillon d'écriture manuelle (équilibré mais non validé).
     *
     * @param  array<int, array{accounting_account_id?: int, debit?: mixed, credit?: mixed, notes?: ?string}>  $lines
     */
    public function createDraft(array $data, array $lines, ?User $actor = null): JournalEntry
    {
        $actor ??= auth()->user();

        return DB::transaction(function () use ($data, $lines, $actor): JournalEntry {
            $normalized = $this->normalizeLines($lines);
            $this->assertBalanced($normalized);

            $entry = JournalEntry::create([
                'entry_number' => $this->generateNumber(),
                'entry_date' => $data['entry_date'] ?? now()->toDateString(),
                'type' => JournalEntryType::Manual->value,
                'description' => $data['description'] ?? null,
                'source_type' => null,
                'source_id' => null,
                'status' => JournalEntryStatus::Draft,
                'created_by' => $actor?->id,
            ]);

            $entry->lines()->createMany($normalized);

            activity('journal')
                ->performedOn($entry)
                ->causedBy($actor)
                ->log('Brouillon d\'écriture comptable créé');

            return $entry->load('lines.account');
        });
    }

    /**
     * Modifie un brouillon d'écriture manuelle (lignes remplacées, montants
     * revalidés et rééquilibrés).
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, array{accounting_account_id?: int, debit?: mixed, credit?: mixed, notes?: ?string}>  $lines
     */
    public function updateDraft(JournalEntry $entry, array $data, array $lines, ?User $actor = null): JournalEntry
    {
        $actor ??= auth()->user();

        abort_unless($entry->status === JournalEntryStatus::Draft, 409, 'Seul un brouillon d\'écriture peut être modifié.');

        return DB::transaction(function () use ($entry, $data, $lines, $actor): JournalEntry {
            $normalized = $this->normalizeLines($lines);
            $this->assertBalanced($normalized);

            $entry->forceFill([
                'entry_date' => $data['entry_date'] ?? $entry->entry_date,
                'description' => $data['description'] ?? null,
            ])->save();

            $entry->lines()->delete();
            $entry->lines()->createMany($normalized);

            activity('journal')
                ->performedOn($entry)
                ->causedBy($actor)
                ->log('Brouillon d\'écriture comptable modifié');

            return $entry->fresh('lines.account');
        });
    }

    /**
     * Valide un brouillon d'écriture manuelle.
     */
    public function postDraft(JournalEntry $entry, ?User $actor = null): JournalEntry
    {
        $actor ??= auth()->user();

        abort_unless($entry->status === JournalEntryStatus::Draft, 409, 'Seul un brouillon peut être saisi.');

        return DB::transaction(function () use ($entry, $actor): JournalEntry {
            $entry->forceFill([
                'status' => JournalEntryStatus::Posted,
                'posted_at' => now(),
            ])->save();

            activity('journal')
                ->performedOn($entry)
                ->causedBy($actor)
                ->log('Écriture comptable saisie');

            return $entry->fresh('lines.account');
        });
    }

    /**
     * Annule un brouillon d'écriture.
     */
    public function cancel(JournalEntry $entry, ?string $reason = null, ?User $actor = null): JournalEntry
    {
        $actor ??= auth()->user();

        abort_unless($entry->status === JournalEntryStatus::Draft, 409, 'Seule une écriture brouillon peut être annulée.');

        return DB::transaction(function () use ($entry, $reason, $actor): JournalEntry {
            $entry->forceFill([
                'status' => JournalEntryStatus::Cancelled,
                'cancelled_at' => now(),
                'cancelled_reason' => $reason,
            ])->save();

            activity('journal')
                ->performedOn($entry)
                ->causedBy($actor)
                ->withProperties(['reason' => $reason])
                ->log('Écriture comptable annulée');

            return $entry->fresh();
        });
    }

    public function hasEntryFor(Model $source, JournalEntryType $type, ?JournalEntryStatus $status = JournalEntryStatus::Posted): bool
    {
        return JournalEntry::query()
            ->where('source_type', $source->getMorphClass())
            ->where('source_id', $source->getKey())
            ->where('type', $type->value)
            ->where('status', $status->value)
            ->exists();
    }

    // ---------------------------------------------------------------------
    // Génération automatique depuis la facturation
    // ---------------------------------------------------------------------

    /**
     * Écriture d'émission d'une facture :
     * Débit  Clients / Crédit  Prestations + TVA collectée.
     */
    public function postInvoiceIssued(Invoice $invoice, ?User $actor = null): ?JournalEntry
    {
        if ($this->hasEntryFor($invoice, JournalEntryType::InvoiceIssue)) {
            return null;
        }

        if (Money::lte((string) $invoice->total, '0')) {
            return null;
        }

        $lines = [
            ['account_code' => AccountCode::RECEIVABLES, 'debit' => $invoice->total],
        ];

        $revenue = Money::sub((string) $invoice->total, (string) $invoice->tax_amount);

        if (Money::gt((string) $invoice->tax_amount, '0')) {
            $lines[] = ['account_code' => AccountCode::VAT_COLLECTED, 'credit' => $invoice->tax_amount];
        }

        if (Money::gt($revenue, '0')) {
            $lines[] = ['account_code' => AccountCode::REVENUE, 'credit' => $revenue];
        }

        return $this->post(
            JournalEntryType::InvoiceIssue->value,
            "Émission de la facture {$invoice->invoice_number}",
            $lines,
            $invoice->invoice_date->toDateString(),
            $invoice,
            $actor,
        );
    }

    /**
     * Écriture d'encaissement :
     * Débit  Caisse/Banque / Crédit  Clients.
     */
    public function postPayment(Payment $payment, ?User $actor = null): ?JournalEntry
    {
        if ($this->hasEntryFor($payment, JournalEntryType::Payment)) {
            return null;
        }

        if (Money::lte((string) $payment->amount, '0')) {
            return null;
        }

        $cashCode = $payment->paymentMethod?->type === PaymentMethodType::Cash
            ? AccountCode::CASH
            : AccountCode::BANK;

        $description = "Encaissement {$payment->payment_number}";

        if ($payment->invoice) {
            $description .= " (facture {$payment->invoice->invoice_number})";
        }

        return $this->post(
            JournalEntryType::Payment->value,
            $description,
            [
                ['account_code' => $cashCode, 'debit' => $payment->amount],
                ['account_code' => AccountCode::RECEIVABLES, 'credit' => $payment->amount],
            ],
            $payment->payment_date->toDateString(),
            $payment,
            $actor,
        );
    }

    /**
     * Écriture d'émission d'un avoir :
     * Débit  RRR accordés / Crédit  Clients.
     */
    public function postCreditNote(CreditNote $creditNote, ?User $actor = null): ?JournalEntry
    {
        if ($this->hasEntryFor($creditNote, JournalEntryType::CreditNote)) {
            return null;
        }

        if (Money::lte((string) $creditNote->amount, '0')) {
            return null;
        }

        return $this->post(
            JournalEntryType::CreditNote->value,
            "Avoir {$creditNote->credit_note_number}".($creditNote->invoice ? " (facture {$creditNote->invoice->invoice_number})" : ''),
            [
                ['account_code' => AccountCode::REVENUE_CONTRA, 'debit' => $creditNote->amount],
                ['account_code' => AccountCode::RECEIVABLES, 'credit' => $creditNote->amount],
            ],
            $creditNote->credit_note_date->toDateString(),
            $creditNote,
            $actor,
        );
    }

    /**
     * Écriture de remboursement :
     * Débit  Clients / Crédit  Caisse/Banque.
     */
    public function postRefund(Refund $refund, ?User $actor = null): ?JournalEntry
    {
        if ($this->hasEntryFor($refund, JournalEntryType::Refund)) {
            return null;
        }

        if (Money::lte((string) $refund->amount, '0')) {
            return null;
        }

        $cashCode = $refund->refund_method === 'cash' ? AccountCode::CASH : AccountCode::BANK;

        return $this->post(
            JournalEntryType::Refund->value,
            "Remboursement {$refund->refund_number}",
            [
                ['account_code' => AccountCode::RECEIVABLES, 'debit' => $refund->amount],
                ['account_code' => $cashCode, 'credit' => $refund->amount],
            ],
            $refund->refund_date->toDateString(),
            $refund,
            $actor,
        );
    }

    /**
     * Annulation d'une facture émise : l'écriture d'émission est annulée
     * (contre-passée) afin de neutraliser son impact sur la balance.
     */
    public function postInvoiceCancelled(Invoice $invoice, ?User $actor = null): ?JournalEntry
    {
        $issueEntry = JournalEntry::query()
            ->where('source_type', $invoice->getMorphClass())
            ->where('source_id', $invoice->getKey())
            ->where('type', JournalEntryType::InvoiceIssue->value)
            ->where('status', JournalEntryStatus::Posted->value)
            ->first();

        if (! $issueEntry) {
            return null;
        }

        return DB::transaction(function () use ($issueEntry, $invoice, $actor): JournalEntry {
            $issueEntry->forceFill([
                'status' => JournalEntryStatus::Cancelled,
                'cancelled_at' => now(),
                'cancelled_reason' => "Annulation de la facture {$invoice->invoice_number}",
            ])->save();

            activity('journal')
                ->performedOn($issueEntry)
                ->causedBy($actor)
                ->log('Écriture comptable annulée (facture annulée)');

            return $issueEntry->fresh();
        });
    }

    // ---------------------------------------------------------------------
    // Aides internes
    // ---------------------------------------------------------------------

    /**
     * Résout et normalise les lignes (comptes + montants en précision 3).
     *
     * @param  array<int, array<string, mixed>>  $lines
     * @return array<int, array{accounting_account_id: int, debit: string, credit: string, notes: ?string}>
     */
    private function normalizeLines(array $lines): array
    {
        $normalized = [];

        foreach ($lines as $line) {
            $account = $this->resolveAccount($line);

            if (! $account) {
                throw new InvalidArgumentException('Compte comptable introuvable ou inactif.');
            }

            $debit = Money::normalize((string) ($line['debit'] ?? 0));
            $credit = Money::normalize((string) ($line['credit'] ?? 0));

            if (Money::lt($debit, '0') || Money::lt($credit, '0')) {
                throw new InvalidArgumentException('Les montants d\'une ligne ne peuvent pas être négatifs.');
            }

            if (Money::gt($debit, '0') && Money::gt($credit, '0')) {
                throw new InvalidArgumentException('Une ligne ne peut pas être à la fois au débit et au crédit.');
            }

            if (Money::lte($debit, '0') && Money::lte($credit, '0')) {
                throw new InvalidArgumentException('Chaque ligne doit comporter un montant.');
            }

            $normalized[] = [
                'accounting_account_id' => $account->id,
                'debit' => $debit,
                'credit' => $credit,
                'notes' => $line['notes'] ?? null,
            ];
        }

        if ($normalized === []) {
            throw new InvalidArgumentException('L\'écriture doit comporter au moins une ligne.');
        }

        return $normalized;
    }

    /**
     * @param  array<string, mixed>  $line
     */
    private function resolveAccount(array $line): ?AccountingAccount
    {
        if (! empty($line['accounting_account_id'])) {
            return AccountingAccount::query()
                ->whereKey((int) $line['accounting_account_id'])
                ->where('is_active', true)
                ->first();
        }

        if (! empty($line['account_code'])) {
            return AccountingAccount::query()
                ->where('code', $line['account_code'])
                ->where('is_active', true)
                ->first();
        }

        return null;
    }

    /**
     * @param  array<int, array{debit: string, credit: string}>  $normalized
     */
    private function assertBalanced(array $normalized): void
    {
        $totalDebit = Money::zero();
        $totalCredit = Money::zero();

        foreach ($normalized as $line) {
            $totalDebit = Money::add($totalDebit, $line['debit']);
            $totalCredit = Money::add($totalCredit, $line['credit']);
        }

        if (Money::compare($totalDebit, $totalCredit) !== 0) {
            throw new InvalidArgumentException('Le total des débits doit être égal au total des crédits.');
        }
    }
}
