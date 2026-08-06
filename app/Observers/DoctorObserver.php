<?php

namespace App\Observers;

use App\Models\Doctor;
use App\Services\DoctorService;
use Illuminate\Support\Facades\Auth;

class DoctorObserver
{
    public function __construct(private readonly DoctorService $service) {}

    public function creating(Doctor $doctor): void
    {
        $doctor->doctor_code ??= $this->service->generateDoctorCode();
    }

    public function created(Doctor $doctor): void
    {
        $this->service->ensureDoctorRole($doctor);

        $this->log($doctor, 'Médecin créé');
    }

    public function updated(Doctor $doctor): void
    {
        $this->log($doctor, 'Médecin modifié');
    }

    public function deleted(Doctor $doctor): void
    {
        $this->log($doctor, 'Médecin supprimé');
    }

    public function restored(Doctor $doctor): void
    {
        $this->log($doctor, 'Médecin restauré');
    }

    public function forceDeleted(Doctor $doctor): void
    {
        $this->log($doctor, 'Médecin supprimé définitivement');
    }

    private function log(Doctor $doctor, string $description): void
    {
        activity('doctors')
            ->performedOn($doctor)
            ->causedBy(Auth::user())
            ->log($description);
    }
}
