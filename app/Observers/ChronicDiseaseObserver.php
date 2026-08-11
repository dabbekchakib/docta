<?php

namespace App\Observers;

use App\Models\ChronicDisease;
use Illuminate\Support\Facades\Auth;

class ChronicDiseaseObserver
{
    public function created(ChronicDisease $disease): void
    {
        activity('medical_records')
            ->performedOn($disease->medicalRecord)
            ->causedBy(Auth::user())
            ->withProperties(['disease' => $disease->disease_name])
            ->log('Maladie chronique ajoutée : '.$disease->disease_name);
    }

    public function updated(ChronicDisease $disease): void
    {
        activity('medical_records')
            ->performedOn($disease->medicalRecord)
            ->causedBy(Auth::user())
            ->withProperties([
                'disease' => $disease->disease_name,
                'changes' => $disease->getChanges(),
            ])
            ->log('Maladie chronique modifiée : '.$disease->disease_name);
    }
}
