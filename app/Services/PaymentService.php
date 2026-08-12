<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\User;
use App\Notifications\PaymentReceivedNotification;
use App\Support\Money;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function __construct(
        private readonly InvoiceService $invoiceService,
        private readonly SettingsService $settings,
    ) {}

    /**
     * Génère un numéro de paiement unique (PAY-000001).
     */
    public function generateNumber(): string
    {
        $prefix = $this->settings->paymentPrefix();
        $sequence = Payment::withTrashed()->count() + 1;

        return "{$prefix}-".str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Enregistre un paiement (acompte ou règlement total) sur une facture.
     *
     * - La facture est verrouillée en écriture (MySQL FOR UPDATE) afin d'éviter
     *   les paiements concurrents.
     * - Le montant est plafonné au restant dû (aucun trop-perçu accepté).
     * - Un reçu (REC-…) est systématiquement émis.
     *
     * @param  array<string, mixed>  $data
     */
    public function record(array $data, ?User $actor = null): Payment
    {
        $actor ??= auth()->user();

        return DB::transaction(function () use ($data, $actor): Payment {
            $invoice = Invoice::query()
                ->whereKey($data['invoice_id'])
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless($invoice->isIssued() && $invoice->status !== InvoiceStatus::Credited, 409, 'Impossible d\'encaisser sur cette facture.');

            $amount = Money::normalize((string) ($data['amount'] ?? 0));

            $this->invoiceService->assertValidDeposit($amount, $invoice);

            $payment = Payment::create([
                'payment_number' => $this->generateNumber(),
                'invoice_id' => $invoice->id,
                'patient_id' => $invoice->patient_id,
                'payment_method_id' => $data['payment_method_id'] ?? null,
                'payment_date' => $data['payment_date'] ?? now()->toDateString(),
                'amount' => $amount,
                'status' => PaymentStatus::Completed,
                'reference' => $data['reference'] ?? null,
                'notes' => $data['notes'] ?? null,
                'received_by' => $actor?->id,
            ]);

            $this->invoiceService->refreshPayments($invoice);

            $receipt = $this->issueReceipt($payment, $actor);

            $invoice->patient?->notify(new PaymentReceivedNotification($payment));

            activity('payments')
                ->performedOn($payment)
                ->causedBy($actor)
                ->withProperties([
                    'payment_number' => $payment->payment_number,
                    'invoice_number' => $invoice->invoice_number,
                    'amount' => $amount,
                    'receipt_number' => $receipt->receipt_number,
                ])
                ->log('Paiement encaissé');

            return $payment->load('invoice', 'paymentMethod', 'receipt');
        });
    }

    /**
     * Annule un paiement encaissé et restaure le solde de la facture.
     */
    public function cancel(Payment $payment, ?string $reason = null, ?User $actor = null): Payment
    {
        $actor ??= auth()->user();

        abort_unless($payment->status === PaymentStatus::Completed, 409, 'Seul un paiement encaissé peut être annulé.');
        abort_if($payment->refunds()->where('status', 'completed')->exists(), 409, 'Ce paiement a déjà été remboursé.');

        return DB::transaction(function () use ($payment, $reason, $actor): Payment {
            $payment->forceFill([
                'status' => PaymentStatus::Cancelled,
                'cancelled_at' => now(),
                'cancelled_reason' => $reason,
            ])->save();

            $payment->receipt?->delete();

            $this->invoiceService->refreshPayments($payment->invoice);

            activity('payments')
                ->performedOn($payment)
                ->causedBy($actor)
                ->withProperties([
                    'payment_number' => $payment->payment_number,
                    'reason' => $reason,
                ])
                ->log('Paiement annulé');

            return $payment->fresh('invoice', 'receipt');
        });
    }

    /**
     * Génère le reçu correspondant à un paiement.
     */
    public function issueReceipt(Payment $payment, ?User $actor = null): Receipt
    {
        return Receipt::create([
            'receipt_number' => $this->generateReceiptNumber(),
            'payment_id' => $payment->id,
            'invoice_id' => $payment->invoice_id,
            'patient_id' => $payment->patient_id,
            'receipt_date' => $payment->payment_date,
            'amount' => $payment->amount,
            'created_by' => $actor?->id,
        ]);
    }

    /**
     * Génère un numéro de reçu unique (REC-000001).
     */
    public function generateReceiptNumber(): string
    {
        $prefix = $this->settings->receiptPrefix();
        $sequence = Receipt::count() + 1;

        return "{$prefix}-".str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
    }
}
