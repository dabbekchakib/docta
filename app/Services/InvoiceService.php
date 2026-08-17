<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Events\InvoiceCancelled;
use App\Events\InvoiceIssued;
use App\Models\Invoice;
use App\Models\User;
use App\Notifications\InvoiceIssuedNotification;
use App\Notifications\InvoiceOverdueNotification;
use App\Support\Money;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    public function __construct(
        private readonly InvoiceCalculationService $calculation,
        private readonly SettingsService $settings,
    ) {}

    /**
     * Vérifie qu'un montant d'acompte est valide pour une facture (délégation).
     */
    public function assertValidDeposit(string $amount, Invoice $invoice): void
    {
        $this->calculation->assertValidDeposit($amount, $invoice);
    }

    /**
     * Génère un numéro de facture unique (FAC-2026-000001).
     */
    public function generateNumber(): string
    {
        $prefix = $this->settings->invoicePrefix();
        $year = now()->format('Y');
        $sequence = Invoice::withTrashed()->count() + 1;

        return "{$prefix}-{$year}-".str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Crée une facture (brouillon) et recalcule tous les montants côté serveur.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, array<string, mixed>>  $items
     */
    public function create(array $data, array $items, ?User $actor = null): Invoice
    {
        $actor ??= auth()->user();

        return DB::transaction(function () use ($data, $items, $actor): Invoice {
            $calculated = $this->calculation->calculate(
                $items,
                (string) ($data['discount_type'] ?? 'none'),
                (string) ($data['discount_value'] ?? 0),
            );

            $invoice = Invoice::create([
                'invoice_number' => $this->generateNumber(),
                'patient_id' => $data['patient_id'],
                'doctor_id' => $data['doctor_id'] ?? null,
                'consultation_id' => $data['consultation_id'] ?? null,
                'appointment_id' => $data['appointment_id'] ?? null,
                'laboratory_request_id' => $data['laboratory_request_id'] ?? null,
                'invoice_date' => $data['invoice_date'] ?? now()->toDateString(),
                'due_date' => $data['due_date'] ?? null,
                'status' => InvoiceStatus::Draft,
                'discount_type' => $calculated['discount_type'],
                'discount_value' => $calculated['discount_value'],
                'subtotal' => $calculated['subtotal'],
                'discount_amount' => $calculated['discount_amount'],
                'taxable_base' => $calculated['taxable_base'],
                'tax_amount' => $calculated['tax_amount'],
                'stamp_fee' => $calculated['stamp_fee'],
                'total' => $calculated['total'],
                'amount_paid' => Money::zero(),
                'amount_remaining' => $calculated['total'],
                'notes' => $data['notes'] ?? null,
                'created_by' => $actor?->id,
            ]);

            $invoice->items()->createMany($calculated['items']);

            $this->log($invoice, $actor, 'Facture créée (brouillon)');

            return $invoice->load('items', 'patient');
        });
    }

    /**
     * Modifie une facture brouillon (contenu + lignes, montants recalculés).
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, array<string, mixed>>  $items
     */
    public function update(Invoice $invoice, array $data, array $items, ?User $actor = null): Invoice
    {
        $actor ??= auth()->user();

        abort_unless($invoice->status === InvoiceStatus::Draft, 409, 'Seule une facture brouillon peut être modifiée.');

        return DB::transaction(function () use ($invoice, $data, $items, $actor): Invoice {
            $calculated = $this->calculation->calculate(
                $items,
                (string) ($data['discount_type'] ?? 'none'),
                (string) ($data['discount_value'] ?? 0),
            );

            $invoice->fill([
                'patient_id' => $data['patient_id'],
                'doctor_id' => $data['doctor_id'] ?? null,
                'consultation_id' => $data['consultation_id'] ?? null,
                'appointment_id' => $data['appointment_id'] ?? null,
                'laboratory_request_id' => $data['laboratory_request_id'] ?? null,
                'invoice_date' => $data['invoice_date'] ?? $invoice->invoice_date,
                'due_date' => $data['due_date'] ?? null,
                'discount_type' => $calculated['discount_type'],
                'discount_value' => $calculated['discount_value'],
                'subtotal' => $calculated['subtotal'],
                'discount_amount' => $calculated['discount_amount'],
                'taxable_base' => $calculated['taxable_base'],
                'tax_amount' => $calculated['tax_amount'],
                'stamp_fee' => $calculated['stamp_fee'],
                'total' => $calculated['total'],
                'amount_remaining' => $calculated['total'],
                'notes' => $data['notes'] ?? null,
            ])->save();

            $invoice->items()->delete();
            $invoice->items()->createMany($calculated['items']);

            $this->log($invoice, $actor, 'Facture modifiée');

            return $invoice->fresh(['items', 'patient']);
        });
    }

    /**
     * Émet une facture brouillon (devient « Émise »).
     */
    public function issue(Invoice $invoice, ?User $actor = null): Invoice
    {
        $actor ??= auth()->user();

        abort_unless($invoice->status === InvoiceStatus::Draft, 409, 'Seule une facture brouillon peut être émise.');

        $invoice->forceFill([
            'status' => InvoiceStatus::Issued,
            'issued_at' => now(),
        ])->save();

        $this->log($invoice, $actor, 'Facture émise');

        $invoice->patient?->notify(new InvoiceIssuedNotification($invoice));

        InvoiceIssued::dispatch($invoice, $actor);

        return $invoice->fresh(['items', 'patient']);
    }

    /**
     * Annule une facture (brouillon ou émise non payée).
     */
    public function cancel(Invoice $invoice, ?string $reason = null, ?User $actor = null): Invoice
    {
        $actor ??= auth()->user();

        abort_if($invoice->status === InvoiceStatus::Cancelled, 409, 'Cette facture est déjà annulée.');
        abort_if($invoice->status === InvoiceStatus::Paid, 409, 'Une facture payée ne peut pas être annulée ; utilisez un avoir.');

        return DB::transaction(function () use ($invoice, $reason, $actor): Invoice {
            $invoice->forceFill([
                'status' => InvoiceStatus::Cancelled,
                'cancelled_at' => now(),
                'cancelled_reason' => $reason,
            ])->save();

            $this->log($invoice, $actor, 'Facture annulée', ['reason' => $reason]);

            if ($invoice->status === InvoiceStatus::Cancelled) {
                InvoiceCancelled::dispatch($invoice, $actor);
            }

            return $invoice->fresh();
        });
    }

    /**
     * Recalcule la répartition entre acomptes payés et restant dû.
     */
    public function refreshPayments(Invoice $invoice): Invoice
    {
        $paid = $invoice->payments()
            ->where('status', 'completed')
            ->sum('amount');

        $amountPaid = Money::normalize((string) $paid);
        $amountRemaining = Money::sub((string) $invoice->total, $amountPaid);

        if (Money::lte($amountRemaining, '0')) {
            $status = InvoiceStatus::Paid;
            $amountRemaining = Money::zero();
        } elseif (Money::gt($amountPaid, '0')) {
            $status = InvoiceStatus::PartiallyPaid;
        } else {
            $status = $invoice->status === InvoiceStatus::Overdue ? InvoiceStatus::Overdue : InvoiceStatus::Issued;
        }

        $invoice->forceFill([
            'amount_paid' => $amountPaid,
            'amount_remaining' => $amountRemaining,
            'status' => $status,
        ])->save();

        return $invoice->fresh();
    }

    /**
     * Marque les factures émises non soldées comme « En retard ».
     */
    public function markOverdue(): int
    {
        $count = 0;

        Invoice::query()
            ->whereIn('status', [InvoiceStatus::Issued, InvoiceStatus::PartiallyPaid])
            ->whereDate('due_date', '<', now()->toDateString())
            ->chunkById(100, function ($invoices) use (&$count): void {
                foreach ($invoices as $invoice) {
                    $invoice->forceFill(['status' => InvoiceStatus::Overdue])->save();
                    $invoice->patient?->notify(new InvoiceOverdueNotification($invoice));
                    $count++;
                }
            });

        return $count;
    }

    private function log(Invoice $invoice, ?User $actor, string $message, array $properties = []): void
    {
        activity('invoices')
            ->performedOn($invoice)
            ->causedBy($actor)
            ->withProperties(['invoice_number' => $invoice->invoice_number, ...$properties])
            ->log($message);
    }
}
