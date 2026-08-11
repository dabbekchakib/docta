<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum MedicalDocumentType: string implements HasLabel
{
    case Analysis = 'analyse';
    case Radiology = 'radiologie';
    case Report = 'compte_rendu';
    case Certificate = 'certificat';
    case Prescription = 'ordonnance';
    case Hospitalisation = 'hospitalisation';
    case MedicalLetter = 'courrier_medical';
    case Other = 'autre';

    public function getLabel(): string
    {
        return match ($this) {
            self::Analysis => 'Analyse biologique',
            self::Radiology => 'Radiologie',
            self::Report => 'Compte rendu',
            self::Certificate => 'Certificat',
            self::Prescription => 'Ordonnance',
            self::Hospitalisation => 'Hospitalisation',
            self::MedicalLetter => 'Courrier médical',
            self::Other => 'Autre',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case): array => [$case->value => $case->getLabel()])
            ->all();
    }
}
