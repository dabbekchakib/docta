<?php

namespace App\Observers;

use App\Enums\AppointmentStatus;
use App\Enums\AppointmentType;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Notifications\AppointmentNotification;
use App\Services\AppointmentService;
use Illuminate\Support\Facades\Auth;

class AppointmentObserver
{
    public function __construct(private readonly AppointmentService $service) {}

    public function creating(Appointment $appointment): void
    {
        $appointment->appointment_number ??= $this->service->generateAppointmentNumber();
        $appointment->status ??= AppointmentStatus::Pending;
        $appointment->type ??= AppointmentType::Consultation;
        $appointment->duration ??= 30;
        $appointment->created_by ??= Auth::id();
    }

    public function saving(Appointment $appointment): void
    {
        if (! $appointment->start_time) {
            return;
        }

        $window = $this->service->resolveTimeWindow($appointment->only(['start_time', 'end_time', 'duration']));

        $appointment->end_time = $window['end_time'];
        $appointment->duration = $window['duration'];
    }

    public function created(Appointment $appointment): void
    {
        $this->notifyDoctor($appointment, 'created');
        $this->log($appointment, 'Rendez-vous créé');
    }

    public function updated(Appointment $appointment): void
    {
        if ($appointment->wasChanged('status')) {
            AppointmentService::notifyStatusChange($appointment);
        }

        $this->log($appointment, 'Rendez-vous modifié');
    }

    public function deleted(Appointment $appointment): void
    {
        $this->log($appointment, 'Rendez-vous supprimé');
    }

    public function restored(Appointment $appointment): void
    {
        $this->log($appointment, 'Rendez-vous restauré');
    }

    public function forceDeleted(Appointment $appointment): void
    {
        $this->log($appointment, 'Rendez-vous supprimé définitivement');
    }

    private function notifyDoctor(Appointment $appointment, string $event): void
    {
        /** @var Doctor|null $doctor */
        $doctor = $appointment->doctor;

        if ($doctor?->user) {
            $doctor->user->notify(new AppointmentNotification($appointment, $event));
        }
    }

    private function log(Appointment $appointment, string $description): void
    {
        activity('appointments')
            ->performedOn($appointment)
            ->causedBy(Auth::user())
            ->log($description);
    }
}
