<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceOverdueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Invoice $invoice,
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
        $invoice = $this->invoice;

        return (new MailMessage)
            ->subject('DOCTA — Facture en retard '.$invoice->invoice_number)
            ->greeting('Bonjour,')
            ->line('La facture '.$invoice->invoice_number.' est arrivée à échéance sans règlement.')
            ->line('Montant restant dû : '.number_format((float) $invoice->amount_remaining, 3, ',', ' ').' DT')
            ->line('Échéance : '.($invoice->due_date?->format('d/m/Y') ?? '—'))
            ->line('Patient : '.($invoice->patient?->full_name ?? '—'))
            ->salutation('L\'équipe DOCTA');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $invoice = $this->invoice;

        return [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'patient' => $invoice->patient?->full_name,
            'amount_remaining' => $invoice->amount_remaining,
            'due_date' => $invoice->due_date?->toDateString(),
            'message' => 'La facture '.$invoice->invoice_number.' est en retard de paiement.',
        ];
    }
}
