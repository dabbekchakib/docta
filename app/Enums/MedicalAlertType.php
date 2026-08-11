<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum MedicalAlertType: string implements HasLabel
{
    case Allergy = 'allergy';
    case ChronicDisease = 'chronic_disease';
    case Medication = 'medication';
    case Other = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::Allergy => 'Allergie',
            self::ChronicDisease => 'Maladie chronique',
            self::Medication => 'Médicament',
            self::Other => 'Autre',
        };
    }
}
