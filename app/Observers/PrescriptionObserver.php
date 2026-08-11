<?php

namespace App\Observers;

use App\Models\Prescription;

/**
 * Journalise la création et la suppression d'une ordonnance.
 */
class PrescriptionObserver
{
    public function created(Prescription $prescription): void
    {
        $prescription->refresh();

        activity('prescriptions')
            ->performedOn($prescription)
            ->causedBy(auth()->user())
            ->withProperties(['prescription_number' => $prescription->prescription_number])
            ->log('Ordonnance créée');
    }

    public function deleting(Prescription $prescription): void
    {
        if ($prescription->isForceDeleting()) {
            return;
        }

        activity('prescriptions')
            ->performedOn($prescription)
            ->causedBy(auth()->user())
            ->withProperties(['prescription_number' => $prescription->prescription_number])
            ->log('Ordonnance supprimée');
    }
}
