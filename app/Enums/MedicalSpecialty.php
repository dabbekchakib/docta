<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum MedicalSpecialty: string implements HasLabel
{
    case General = 'Médecine générale';
    case Cardiology = 'Cardiologie';
    case Pediatrics = 'Pédiatrie';
    case Gynecology = 'Gynécologie';
    case Dermatology = 'Dermatologie';
    case Orthopedics = 'Orthopédie';
    case Otorhinolaryngology = 'ORL';
    case Ophthalmology = 'Ophtalmologie';
    case Urology = 'Urologie';
    case Neurology = 'Neurologie';
    case Pulmonology = 'Pneumologie';
    case Endocrinology = 'Endocrinologie';
    case Gastroenterology = 'Gastro-entérologie';
    case Rheumatology = 'Rhumatologie';
    case Psychiatry = 'Psychiatrie';

    public function getLabel(): string
    {
        return $this->value;
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case): array => [$case->value => $case->value])
            ->all();
    }
}
