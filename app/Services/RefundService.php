<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Enums\RefundStatus;
use App\Events\RefundCompleted;
use App\Models\CreditNote;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\User;
use App\Notifications\RefundApprovedNotification;
use App\Notifications\RefundRejectedNotification;
use App\Support\Money;
use Illuminate\Support\Facades\DB;

class RefundService
{
    public function __construct(
        private readonly SettingsService $settings,
    ) {}

    /**
     * Génère un numéro de remboursement unique (REM-000001).
     */
    public function generateNumber(): string
    {
        $prefix = $this->settings->refundPrefix();
        $sequence = Refund::withTrashed()->count() + 1;

        return "{$prefix}-".str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Demande un remboursement (montant plafonné au paiement encaissé).
     *
     * @param  array<string, mixed>  $data
     */
    public function request(Payment $payment, array $data, ?User $actor = null): Refund
    {
        $actor ??= auth()->user();

        abort_unless($payment->status === PaymentStatus::Completed, 409, 'Un remboursement ne peut porter que sur un paiement encaissé.');

        $amount = Money::normalize((string) ($data['amount'] ?? '0'));

        abort_if(Money::lte($amount, '0'), 422, 'Le montant du remboursement doit être supérieur à zéro.');
        abort_if(Money::gt($amount, (string) $payment->amount), 422, 'Le remboursement dépasse le montant du paiement.');

        $refundable = $this->refundableBalance($payment);

        abort_if(Money::gt($amount, $refundable), 422, 'Le montant dépasse le solde remboursable de ce paiement.');

        return DB::transaction(function () use ($payment, $data, $amount, $actor): Refund {
            $refund = Refund::create([
                'refund_number' => $this->generateNumber(),
                'payment_id' => $payment->id,
                'credit_note_id' => $data['credit_note_id'] ?? null,
                'patient_id' => $payment->patient_id,
                'refund_date' => $data['refund_date'] ?? now()->toDateString(),
                'amount' => $amount,
                'reason' => $data['reason'] ?? null,
                'status' => RefundStatus::Pending,
                'refund_method' => $data['refund_method'] ?? null,
                'reference' => $data['reference'] ?? null,
                'requested_at' => now(),
                'created_by' => $actor?->id,
            ]);

            activity('refunds')
                ->performedOn($refund)
                ->causedBy($actor)
                ->withProperties([
                    'refund_number' => $refund->refund_number,
                    'payment_number' => $payment->payment_number,
                    'amount' => $amount,
                ])
                ->log('Remboursement demandé');

            return $refund->load('payment', 'patient');
        });
    }

    public function approve(Refund $refund, ?User $actor = null): Refund
    {
        $actor ??= auth()->user();

        abort_unless($refund->status === RefundStatus::Pending, 409, 'Seule une demande en attente peut être approuvée.');

        $refund->forceFill([
            'status' => RefundStatus::Approved,
            'approved_at' => now(),
            'approved_by' => $actor?->id,
        ])->save();

        $this->log($refund, $actor, 'Remboursement approuvé');

        $refund->patient?->notify(new RefundApprovedNotification($refund));

        return $refund->fresh();
    }

    public function reject(Refund $refund, ?string $reason = null, ?User $actor = null): Refund
    {
        $actor ??= auth()->user();

        abort_unless($refund->status === RefundStatus::Pending, 409, 'Seule une demande en attente peut être refusée.');

        $refund->forceFill([
            'status' => RefundStatus::Rejected,
            'rejected_at' => now(),
            'rejected_reason' => $reason,
        ])->save();

        $this->log($refund, $actor, 'Remboursement refusé', ['reason' => $reason]);

        $refund->patient?->notify(new RefundRejectedNotification($refund));

        return $refund->fresh();
    }

    /**
     * Exécute le remboursement approuvé (solder le paiement et l'avoir éventuel).
     */
    public function execute(Refund $refund, ?User $actor = null): Refund
    {
        $actor ??= auth()->user();

        abort_unless($refund->status === RefundStatus::Approved, 409, 'Seul un remboursement approuvé peut être exécuté.');

        return DB::transaction(function () use ($refund, $actor): Refund {
            $payment = $refund->payment;

            $refund->forceFill([
                'status' => RefundStatus::Completed,
                'completed_at' => now(),
            ])->save();

            $remainingRefundable = Money::sub(
                (string) $payment->amount,
                (string) $refund->amount,
            );

            if (Money::lte($remainingRefundable, '0')) {
                $payment->forceFill(['status' => PaymentStatus::Refunded])->save();
            }

            if ($refund->credit_note_id) {
                $creditNote = CreditNote::find($refund->credit_note_id);

                if ($creditNote && $creditNote->refunds()->where('status', RefundStatus::Completed)->count() === 0) {
                    $this->attachCreditNote($refund, $creditNote);
                }
            }

            $this->log($refund, $actor, 'Remboursement exécuté');

            RefundCompleted::dispatch($refund, $actor);

            return $refund->fresh('payment', 'creditNote');
        });
    }

    public function cancel(Refund $refund, ?string $reason = null, ?User $actor = null): Refund
    {
        $actor ??= auth()->user();

        abort_unless(in_array($refund->status, [RefundStatus::Pending, RefundStatus::Approved], true), 409, 'Ce remboursement est déjà clôturé.');

        $refund->forceFill([
            'status' => RefundStatus::Cancelled,
            'cancelled_at' => now(),
        ])->save();

        $this->log($refund, $actor, 'Remboursement annulé', ['reason' => $reason]);

        return $refund->fresh();
    }

    /**
     * Solde encore remboursable d'un paiement (montant encaissé - remboursements effectués).
     */
    public function refundableBalance(Payment $payment): string
    {
        $refunded = Refund::query()
            ->where('payment_id', $payment->id)
            ->where('status', RefundStatus::Completed)
            ->sum('amount');

        return Money::sub((string) $payment->amount, (string) $refunded);
    }

    private function attachCreditNote(Refund $refund, CreditNote $creditNote): void
    {
        // L'avoir reste lié au remboursement pour l'historique.
    }

    private function log(Refund $refund, ?User $actor, string $message, array $properties = []): void
    {
        activity('refunds')
            ->performedOn($refund)
            ->causedBy($actor)
            ->withProperties(['refund_number' => $refund->refund_number, ...$properties])
            ->log($message);
    }
}
