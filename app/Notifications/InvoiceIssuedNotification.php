<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceIssuedNotification extends Notification implements ShouldQueue
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
            ->subject('DOCTA — Facture '.$invoice->invoice_number)
            ->greeting('Bonjour,')
            ->line('La facture '.$invoice->invoice_number.' a été émise.')
            ->line('Montant total : '.number_format((float) $invoice->total, 3, ',', ' ').' DT')
            ->line('Patient : '.($invoice->patient?->full_name ?? '—'))
            ->line('Vous pouvez consulter le détail dans votre espace DOCTA.')
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
            'total' => $invoice->total,
            'message' => 'La facture '.$invoice->invoice_number.' a été émise.',
        ];
    }
}
