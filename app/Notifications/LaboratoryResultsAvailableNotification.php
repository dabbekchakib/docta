<?php

namespace App\Notifications;

use App\Models\LaboratoryRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LaboratoryResultsAvailableNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly LaboratoryRequest $request,
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
        $request = $this->request;

        return (new MailMessage)
            ->subject('DOCTA — Résultats disponibles ('.$request->request_number.')')
            ->greeting('Bonjour,')
            ->line('Les résultats de la demande '.$request->request_number.' sont disponibles.')
            ->line('Patient : '.($request->patient?->full_name ?? '—'))
            ->line('Date : '.($request->requested_at?->format('d/m/Y') ?? '—'))
            ->line('Vous pouvez consulter le compte rendu dans votre espace DOCTA.')
            ->salutation('L\'équipe DOCTA');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $request = $this->request;

        return [
            'laboratory_request_id' => $request->id,
            'request_number' => $request->request_number,
            'patient' => $request->patient?->full_name,
            'laboratory' => $request->laboratory?->name,
            'requested_at' => $request->requested_at?->toDateString(),
            'priority' => $request->priority?->value,
            'message' => 'Les résultats de la demande '.$request->request_number.' sont disponibles.',
        ];
    }
}
