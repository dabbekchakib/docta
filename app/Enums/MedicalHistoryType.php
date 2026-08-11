<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum MedicalHistoryType: string implements HasLabel
{
    case Disease = 'maladie';
    case Infection = 'infection';
    case Trauma = 'traumatisme';
    case Hospitalisation = 'hospitalisation';
    case Other = 'autre';

    public function getLabel(): string
    {
        return match ($this) {
            self::Disease => 'Maladie',
            self::Infection => 'Infection',
            self::Trauma => 'Traumatisme',
            self::Hospitalisation => 'Hospitalisation',
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
