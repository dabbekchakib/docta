<?php

namespace App\Notifications;

use App\Models\Refund;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RefundApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Refund $refund,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $refund = $this->refund;

        return (new MailMessage)
            ->subject('DOCTA — Remboursement approuvé ('.$refund->refund_number.')')
            ->greeting('Bonjour,')
            ->line('Votre demande de remboursement '.$refund->refund_number.' a été approuvée.')
            ->line('Montant : '.number_format((float) $refund->amount, 3, ',', ' ').' DT')
            ->line('Patient : '.($refund->patient?->full_name ?? '—'))
            ->salutation('L\'équipe DOCTA');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $refund = $this->refund;

        return [
            'refund_id' => $refund->id,
            'refund_number' => $refund->refund_number,
            'patient' => $refund->patient?->full_name,
            'amount' => $refund->amount,
            'message' => 'Le remboursement '.$refund->refund_number.' a été approuvé.',
        ];
    }
}
