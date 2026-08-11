<?php

namespace App\Observers;

use App\Models\Patient;
use App\Services\MedicalRecordService;
use Illuminate\Support\Facades\Auth;

class PatientObserver
{
    public function __construct(private readonly MedicalRecordService $medicalRecords) {}

    public function creating(Patient $patient): void
    {
        $patient->patient_number ??= $this->generatePatientNumber();
    }

    public function created(Patient $patient): void
    {
        $this->medicalRecords->ensureForPatient($patient);

        $this->log($patient, 'Patient créé');
    }

    public function updated(Patient $patient): void
    {
        $this->log($patient, 'Patient modifié');
    }

    public function deleted(Patient $patient): void
    {
        $this->log($patient, 'Patient supprimé');
    }

    public function restored(Patient $patient): void
    {
        $this->log($patient, 'Patient restauré');
    }

    public function forceDeleted(Patient $patient): void
    {
        $this->log($patient, 'Patient supprimé définitivement');
    }

    private function generatePatientNumber(): string
    {
        $sequence = Patient::withTrashed()->max('id') ?? 0;

        return 'PAT-'.str_pad((string) ($sequence + 1), 6, '0', STR_PAD_LEFT);
    }

    private function log(Patient $patient, string $description): void
    {
        activity('patients')
            ->performedOn($patient)
            ->causedBy(Auth::user())
            ->log($description);
    }
}
