<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum MedicineRoute: string implements HasLabel
{
    case Oral = 'orale';
    case Intravenous = 'intraveineuse';
    case Intramuscular = 'intramusculaire';
    case Subcutaneous = 'sous_cutanee';
    case Cutaneous = 'cutanee';
    case Nasal = 'nasale';
    case Inhalation = 'inhalation';
    case Ocular = 'oculaire';
    case Auricular = 'auriculaire';
    case Rectal = 'rectale';
    case Other = 'autre';

    public function label(): string
    {
        return match ($this) {
            self::Oral => 'Orale',
            self::Intravenous => 'Intraveineuse',
            self::Intramuscular => 'Intramusculaire',
            self::Subcutaneous => 'Sous-cutanée',
            self::Cutaneous => 'Cutanée',
            self::Nasal => 'Nasale',
            self::Inhalation => 'Inhalation',
            self::Ocular => 'Oculaire',
            self::Auricular => 'Auriculaire',
            self::Rectal => 'Rectale',
            self::Other => 'Autre',
        };
    }

    public function getLabel(): string
    {
        return $this->label();
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
