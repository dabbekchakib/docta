<?php

namespace App\Services;

use App\Enums\CreditNoteStatus;
use App\Enums\InvoiceStatus;
use App\Models\CreditNote;
use App\Models\Invoice;
use App\Models\User;
use App\Support\Money;
use Illuminate\Support\Facades\DB;

class CreditNoteService
{
    public function __construct(
        private readonly SettingsService $settings,
    ) {}

    /**
     * Génère un numéro d'avoir unique (AV-2026-000001).
     */
    public function generateNumber(): string
    {
        $prefix = $this->settings->creditNotePrefix();
        $year = now()->format('Y');
        $sequence = CreditNote::withTrashed()->count() + 1;

        return "{$prefix}-{$year}-".str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Crée un avoir sur une facture émise (non annulée).
     *
     * @param  array<string, mixed>  $data
     */
    public function create(Invoice $invoice, array $data, ?User $actor = null): CreditNote
    {
        $actor ??= auth()->user();

        abort_unless($invoice->isIssued(), 409, 'Un avoir ne peut être créé que sur une facture émise.');

        $amount = Money::normalize((string) ($data['amount'] ?? '0'));

        abort_if(Money::lte($amount, '0'), 422, 'Le montant de l\'avoir doit être supérieur à zéro.');

        $alreadyCredited = CreditNote::query()
            ->where('invoice_id', $invoice->id)
            ->where('status', CreditNoteStatus::Issued)
            ->sum('amount');

        $creditBalance = Money::sub((string) $invoice->total, (string) $alreadyCredited);

        abort_if(Money::gt($amount, $creditBalance), 422, 'Le montant de l\'avoir dépasse le solde créditable de la facture.');

        return DB::transaction(function () use ($invoice, $data, $amount, $actor): CreditNote {
            $creditNote = CreditNote::create([
                'credit_note_number' => $this->generateNumber(),
                'invoice_id' => $invoice->id,
                'patient_id' => $invoice->patient_id,
                'credit_note_date' => $data['credit_note_date'] ?? now()->toDateString(),
                'amount' => $amount,
                'reason' => $data['reason'] ?? null,
                'status' => CreditNoteStatus::Draft,
                'created_by' => $actor?->id,
            ]);

            activity('credit_notes')
                ->performedOn($creditNote)
                ->causedBy($actor)
                ->withProperties([
                    'credit_note_number' => $creditNote->credit_note_number,
                    'invoice_number' => $invoice->invoice_number,
                    'amount' => $amount,
                ])
                ->log('Avoir créé (brouillon)');

            return $creditNote->load('invoice');
        });
    }

    /**
     * Émet un avoir brouillon.
     */
    public function issue(CreditNote $creditNote, ?User $actor = null): CreditNote
    {
        $actor ??= auth()->user();

        abort_unless($creditNote->status === CreditNoteStatus::Draft, 409, 'Seul un avoir brouillon peut être émis.');

        return DB::transaction(function () use ($creditNote, $actor): CreditNote {
            $creditNote->forceFill([
                'status' => CreditNoteStatus::Issued,
                'issued_at' => now(),
            ])->save();

            $invoice = $creditNote->invoice;

            if ($invoice->status !== InvoiceStatus::Cancelled) {
                $totalCredited = CreditNote::query()
                    ->where('invoice_id', $invoice->id)
                    ->where('status', CreditNoteStatus::Issued)
                    ->sum('amount');

                if (Money::gte((string) $totalCredited, (string) $invoice->total)) {
                    $invoice->forceFill(['status' => InvoiceStatus::Credited])->save();
                }
            }

            activity('credit_notes')
                ->performedOn($creditNote)
                ->causedBy($actor)
                ->withProperties(['credit_note_number' => $creditNote->credit_note_number])
                ->log('Avoir émis');

            return $creditNote->fresh('invoice');
        });
    }

    /**
     * Annule un avoir (brouillon ou émis, sans remboursement associé).
     */
    public function cancel(CreditNote $creditNote, ?string $reason = null, ?User $actor = null): CreditNote
    {
        $actor ??= auth()->user();

        abort_unless(in_array($creditNote->status, [CreditNoteStatus::Draft, CreditNoteStatus::Issued], true), 409, 'Cet avoir est déjà clôturé.');
        abort_if($creditNote->refunds()->where('status', 'completed')->exists(), 409, 'Cet avoir est lié à un remboursement effectué.');

        return DB::transaction(function () use ($creditNote, $reason, $actor): CreditNote {
            $creditNote->forceFill([
                'status' => CreditNoteStatus::Cancelled,
                'cancelled_at' => now(),
                'cancelled_reason' => $reason,
            ])->save();

            activity('credit_notes')
                ->performedOn($creditNote)
                ->causedBy($actor)
                ->withProperties(['credit_note_number' => $creditNote->credit_note_number])
                ->log('Avoir annulé');

            return $creditNote->fresh();
        });
    }
}
