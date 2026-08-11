<?php

namespace App\Listeners;

use App\Events\ConsultationCreated;
use App\Models\Doctor;
use App\Notifications\ConsultationNotification;

class NotifyConsultationDoctor
{
    public function handle(ConsultationCreated $event): void
    {
        /** @var Doctor|null $doctor */
        $doctor = $event->consultation->doctor;

        if ($doctor?->user) {
            $doctor->user->notify(new ConsultationNotification($event->consultation));
        }
    }
}
