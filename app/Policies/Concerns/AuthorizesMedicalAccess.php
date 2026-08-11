<?php

namespace App\Policies\Concerns;

use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\User;

/**
 * Autorisation médicale commune : l'accès du médecin est limité
 * aux patients qu'il suit (rendez-vous ou consultations).
 */
trait AuthorizesMedicalAccess
{
    /**
     * Le médecin suit-il ce patient ?
     */
    protected function isDoctorOfPatient(User $user, MedicalRecord $record): bool
    {
        $doctorId = Doctor::query()->where('user_id', $user->id)->value('id');

        if (! $doctorId) {
            return false;
        }

        $patientId = $record->patient_id;

        return Consultation::query()
                ->where('doctor_id', $doctorId)
                ->where('patient_id', $patientId)
                ->exists()
            || Appointment::query()
                ->where('doctor_id', $doctorId)
                ->where('patient_id', $patientId)
                ->exists();
    }

    /**
     * Récupère le dossier médical associé à un modèle médical.
     */
    protected function medicalRecordOf(mixed $model): MedicalRecord
    {
        if ($model instanceof MedicalRecord) {
            return $model;
        }

        return $model->medicalRecord;
    }

    protected function isDoctor(User $user): bool
    {
        return $user->hasRole('doctor');
    }
}
