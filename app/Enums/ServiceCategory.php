<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ServiceCategory: string implements HasColor, HasLabel
{
    case Consultation = 'consultation';
    case Laboratory = 'laboratory';
    case Imaging = 'imaging';
    case Procedure = 'procedure';
    case Medication = 'medication';
    case Hospitalization = 'hospitalization';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Consultation => 'Consultation',
            self::Laboratory => 'Analyses de laboratoire',
            self::Imaging => 'Imagerie',
            self::Procedure => 'Acte médical',
            self::Medication => 'Médicament',
            self::Hospitalization => 'Hospitalisation',
            self::Other => 'Autre prestation',
        };
    }

    public function getLabel(): string
    {
        return $this->label();
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Consultation => 'primary',
            self::Laboratory => 'info',
            self::Imaging => 'warning',
            self::Procedure => 'success',
            self::Medication => 'gray',
            self::Hospitalization => 'danger',
            self::Other => 'gray',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case): array => [$case->value => $case->label()])
            ->all();
    }
}
