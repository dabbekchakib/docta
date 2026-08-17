<?php

namespace App\Filament\Patient\Pages\Concerns;

use App\Models\Patient;

trait HasPatient
{
    protected function getPatient(): ?Patient
    {
        return auth()->user()->patient;
    }
}
