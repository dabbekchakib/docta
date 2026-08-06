<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Appointment $appointment,
        public readonly string $event,
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
        $appointment = $this->appointment;
        $patient = $appointment->patient;
        $doctor = $appointment->doctor;
        $date = $appointment->appointment_date?->format('d/m/Y') ?? '—';

        $subject = match ($this->event) {
            'created' => 'Nouveau rendez-vous programmé',
            'status' => match ($appointment->status->value) {
                'confirmed' => 'Rendez-vous confirmé',
                'cancelled' => 'Rendez-vous annulé',
                'absent' => 'Patient absent au rendez-vous',
                default => 'Mise à jour de rendez-vous',
            },
            default => 'Mise à jour de rendez-vous',
        };

        return (new MailMessage)
            ->subject("DOCTA - {$subject} ({$appointment->appointment_number})")
            ->greeting('Bonjour,')
            ->line("Patient : {$patient?->full_name}")
            ->line("Médecin : {$doctor?->full_name}")
            ->line("Date : {$date} de {$appointment->start_time} à {$appointment->end_time}")
            ->line("Statut : {$appointment->status->label()}")
            ->line('Vous pouvez consulter le détail dans votre espace DOCTA.')
            ->salutation('L\'équipe DOCTA');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $appointment = $this->appointment;

        return [
            'appointment_id' => $appointment->id,
            'appointment_number' => $appointment->appointment_number,
            'patient' => $appointment->patient?->full_name,
            'doctor' => $appointment->doctor?->full_name,
            'appointment_date' => $appointment->appointment_date?->toDateString(),
            'start_time' => $appointment->start_time,
            'end_time' => $appointment->end_time,
            'status' => $appointment->status->value,
            'event' => $this->event,
        ];
    }
}
