<?php

namespace App\Notifications;

use App\Models\Consultation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ConsultationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Consultation $consultation) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $consultation = $this->consultation;
        $date = $consultation->consultation_date?->format('d/m/Y') ?? '—';

        return (new MailMessage)
            ->subject("DOCTA - Nouvelle consultation ({$consultation->consultation_number})")
            ->greeting('Bonjour,')
            ->line("Une nouvelle consultation a été enregistrée pour {$consultation->patient?->full_name}.")
            ->line("Médecin : {$consultation->doctor?->full_name}")
            ->line("Date : {$date}")
            ->line("Type : {$consultation->type?->label()}")
            ->line("Statut : {$consultation->status?->label()}")
            ->line('Vous pouvez consulter le dossier dans votre espace DOCTA.')
            ->salutation('L\'équipe DOCTA');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $consultation = $this->consultation;

        return [
            'consultation_id' => $consultation->id,
            'consultation_number' => $consultation->consultation_number,
            'patient' => $consultation->patient?->full_name,
            'doctor' => $consultation->doctor?->full_name,
            'consultation_date' => $consultation->consultation_date?->toDateString(),
            'type' => $consultation->type?->value,
            'status' => $consultation->status?->value,
        ];
    }
}
