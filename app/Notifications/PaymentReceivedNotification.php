<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Payment $payment,
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
        $payment = $this->payment;

        return (new MailMessage)
            ->subject('DOCTA — Paiement reçu ('.$payment->payment_number.')')
            ->greeting('Bonjour,')
            ->line('Un paiement de '.number_format((float) $payment->amount, 3, ',', ' ').' DT a été reçu.')
            ->line('N° paiement : '.$payment->payment_number)
            ->line('Facture : '.($payment->invoice?->invoice_number ?? '—'))
            ->line('Reçu : '.($payment->receipt?->receipt_number ?? '—'))
            ->salutation('L\'équipe DOCTA');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $payment = $this->payment;

        return [
            'payment_id' => $payment->id,
            'payment_number' => $payment->payment_number,
            'invoice_number' => $payment->invoice?->invoice_number,
            'patient' => $payment->patient?->full_name,
            'amount' => $payment->amount,
            'message' => 'Paiement '.$payment->payment_number.' reçu ('.number_format((float) $payment->amount, 3, ',', ' ').' DT).',
        ];
    }
}
