<?php

namespace App\Observers;

use App\Models\Allergy;
use Illuminate\Support\Facades\Auth;

class AllergyObserver
{
    public function created(Allergy $allergy): void
    {
        activity('medical_records')
            ->performedOn($allergy->medicalRecord)
            ->causedBy(Auth::user())
            ->withProperties(['allergy' => $allergy->allergen])
            ->log('Allergie ajoutée : '.$allergy->allergen);
    }

    public function updated(Allergy $allergy): void
    {
        activity('medical_records')
            ->performedOn($allergy->medicalRecord)
            ->causedBy(Auth::user())
            ->withProperties([
                'allergy' => $allergy->allergen,
                'changes' => $allergy->getChanges(),
            ])
            ->log('Allergie modifiée : '.$allergy->allergen);
    }

    public function deleted(Allergy $allergy): void
    {
        activity('medical_records')
            ->performedOn($allergy->medicalRecord)
            ->causedBy(Auth::user())
            ->withProperties(['allergy' => $allergy->allergen])
            ->log('Allergie supprimée : '.$allergy->allergen);
    }
}
