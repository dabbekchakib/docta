<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum AllergyType: string implements HasLabel
{
    case Medication = 'medicament';
    case Food = 'aliment';
    case Pollen = 'pollen';
    case Latex = 'latex';
    case Animal = 'animal';
    case Other = 'autre';

    public function getLabel(): string
    {
        return match ($this) {
            self::Medication => 'Médicament',
            self::Food => 'Aliment',
            self::Pollen => 'Pollen',
            self::Latex => 'Latex',
            self::Animal => 'Animal',
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
