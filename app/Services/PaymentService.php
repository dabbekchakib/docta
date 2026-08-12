<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Events\PaymentCompleted;
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
     * Crée un paiement sur une facture émise.
     *
     * - Statut « pending » (défaut) : le paiement est en attente, modifiable,
     *   aucun impact sur la facture, aucun reçu.
     * - Statut « completed » : le paiement est validé immédiatement
     *   (verrouillage, plafonnement, reçu, mise à jour de la facture).
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, ?User $actor = null): Payment
    {
        $actor ??= auth()->user();

        $invoice = Invoice::query()->whereKey($data['invoice_id'])->firstOrFail();

        abort_unless($invoice->isIssued() && $invoice->status !== InvoiceStatus::Credited, 409, 'Impossible d\'encaisser sur cette facture.');

        $payment = Payment::create([
            'payment_number' => $this->generateNumber(),
            'invoice_id' => $invoice->id,
            'patient_id' => $invoice->patient_id,
            'payment_method_id' => $data['payment_method_id'] ?? null,
            'payment_date' => $data['payment_date'] ?? now()->toDateString(),
            'amount' => Money::normalize((string) ($data['amount'] ?? '0')),
            'status' => $data['status'] ?? PaymentStatus::Pending,
            'reference' => $data['reference'] ?? null,
            'notes' => $data['notes'] ?? null,
            'received_by' => $actor?->id,
        ]);

        $this->log($payment, $actor, 'Paiement enregistré (en attente)');

        if ($payment->status === PaymentStatus::Completed) {
            return $this->settle($payment, $actor);
        }

        return $payment->load('invoice', 'paymentMethod');
    }

    /**
     * Modifie un paiement en attente (pending).
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Payment $payment, array $data, ?User $actor = null): Payment
    {
        $actor ??= auth()->user();

        abort_unless($payment->status === PaymentStatus::Pending, 409, 'Seul un paiement en attente peut être modifié.');

        $payment->fill([
            'payment_method_id' => $data['payment_method_id'] ?? $payment->payment_method_id,
            'payment_date' => $data['payment_date'] ?? $payment->payment_date,
            'amount' => Money::normalize((string) ($data['amount'] ?? $payment->amount)),
            'reference' => array_key_exists('reference', $data) ? $data['reference'] : $payment->reference,
            'notes' => array_key_exists('notes', $data) ? $data['notes'] : $payment->notes,
        ])->save();

        $this->log($payment, $actor, 'Paiement en attente modifié');

        if (($data['status'] ?? PaymentStatus::Pending) === PaymentStatus::Completed) {
            return $this->settle($payment->fresh(), $actor);
        }

        return $payment->fresh('invoice', 'paymentMethod');
    }

    /**
     * Valide un paiement en attente.
     *
     * Le règlement est effectué dans une transaction avec verrouillage MySQL
     * (FOR UPDATE) de la facture afin d'éviter les doubles encaissements
     * concurrents :
     *
     * 1. Vérifier que la facture est valide (émise, non créditée).
     * 2. Verrouiller la facture.
     * 3. Recalculer le total réellement payé.
     * 4. Calculer le reste réel.
     * 5. Vérifier que le paiement ne dépasse pas le reste.
     * 6. Enregistrer le paiement comme effectué.
     * 7. Recalculer paid_amount.
     * 8. Recalculer remaining_amount.
     * 9. Mettre à jour le statut de la facture.
     * 10. Créer automatiquement le reçu.
     * 11. Journaliser l'opération.
     */
    public function validate(Payment $payment, ?User $actor = null): Payment
    {
        $actor ??= auth()->user();

        abort_unless($payment->status === PaymentStatus::Pending, 409, 'Seul un paiement en attente peut être validé.');

        return $this->settle($payment, $actor);
    }

    /**
     * Enregistre un paiement déjà encaissé (raccourci « encaissement direct »).
     *
     * @param  array<string, mixed>  $data
     */
    public function record(array $data, ?User $actor = null): Payment
    {
        return $this->create([...$data, 'status' => PaymentStatus::Completed], $actor);
    }

    /**
     * Annule un paiement (en attente ou encaissé) et restaure le solde de la
     * facture si le paiement avait été validé.
     */
    public function cancel(Payment $payment, ?string $reason = null, ?User $actor = null): Payment
    {
        $actor ??= auth()->user();

        abort_unless(in_array($payment->status, [PaymentStatus::Pending, PaymentStatus::Completed], true), 409, 'Seul un paiement en attente ou encaissé peut être annulé.');
        abort_if($payment->status === PaymentStatus::Completed && $payment->refunds()->where('status', 'completed')->exists(), 409, 'Ce paiement a déjà été remboursé.');

        return DB::transaction(function () use ($payment, $reason, $actor): Payment {
            $wasCompleted = $payment->status === PaymentStatus::Completed;

            $payment->forceFill([
                'status' => PaymentStatus::Cancelled,
                'cancelled_at' => now(),
                'cancelled_reason' => $reason,
            ])->save();

            if ($wasCompleted) {
                $payment->receipt?->delete();
                $this->invoiceService->refreshPayments($payment->invoice);
            }

            $this->log($payment, $actor, 'Paiement annulé', ['reason' => $reason]);

            return $payment->fresh('invoice', 'receipt');
        });
    }

    /**
     * Règlement d'un paiement : vérifications, reçu, mise à jour de la facture
     * et journalisation, le tout dans une transaction avec verrouillage.
     */
    private function settle(Payment $payment, ?User $actor = null): Payment
    {
        $actor ??= auth()->user();

        return DB::transaction(function () use ($payment, $actor): Payment {
            $invoice = Invoice::query()
                ->whereKey($payment->invoice_id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless($invoice->isIssued() && $invoice->status !== InvoiceStatus::Credited, 409, 'Impossible d\'encaisser sur cette facture.');

            $paidSoFar = $invoice->payments()
                ->where('status', PaymentStatus::Completed)
                ->where('id', '!=', $payment->id)
                ->sum('amount');

            $remaining = Money::sub((string) $invoice->total, (string) $paidSoFar);

            abort_if(Money::lte($payment->amount, '0'), 422, 'Le montant doit être supérieur à zéro.');
            abort_if(Money::gt($payment->amount, $remaining), 422, 'Le montant dépasse le solde restant dû de la facture.');

            $payment->forceFill(['status' => PaymentStatus::Completed])->save();

            $receipt = $this->issueReceipt($payment, $actor);

            $this->invoiceService->refreshPayments($invoice);

            $invoice->patient?->notify(new PaymentReceivedNotification($payment));

            $this->log($payment, $actor, 'Paiement validé', [
                'invoice_number' => $invoice->invoice_number,
                'amount' => (string) $payment->amount,
                'receipt_number' => $receipt->receipt_number,
            ]);

            PaymentCompleted::dispatch($payment, $actor);

            return $payment->load('invoice', 'paymentMethod', 'receipt');
        });
    }

    /**
     * Génère le reçu correspondant à un paiement.
     */
    public function issueReceipt(Payment $payment, ?User $actor = null): Receipt
    {
        $receipt = Receipt::create([
            'receipt_number' => $this->generateReceiptNumber(),
            'payment_id' => $payment->id,
            'invoice_id' => $payment->invoice_id,
            'patient_id' => $payment->patient_id,
            'receipt_date' => $payment->payment_date,
            'amount' => $payment->amount,
            'created_by' => $actor?->id,
        ]);

        activity('receipts')
            ->performedOn($receipt)
            ->causedBy($actor)
            ->withProperties([
                'receipt_number' => $receipt->receipt_number,
                'payment_number' => $payment->payment_number,
                'amount' => (string) $receipt->amount,
            ])
            ->log('Reçu émis');

        return $receipt;
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

    private function log(Payment $payment, ?User $actor, string $message, array $properties = []): void
    {
        activity('payments')
            ->performedOn($payment)
            ->causedBy($actor)
            ->withProperties(['payment_number' => $payment->payment_number, ...$properties])
            ->log($message);
    }
}
