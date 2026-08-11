<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum MedicineForm: string implements HasLabel
{
    case Tablet = 'tablet';
    case Capsule = 'capsule';
    case Syrup = 'syrup';
    case Injection = 'injection';
    case Inhalation = 'inhalation';
    case Cream = 'cream';
    case Ointment = 'ointment';
    case Gel = 'gel';
    case Drops = 'drops';
    case Suppository = 'suppository';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Tablet => 'Comprimé',
            self::Capsule => 'Gélule',
            self::Syrup => 'Sirop',
            self::Injection => 'Injections',
            self::Inhalation => 'Inhalation',
            self::Cream => 'Crème',
            self::Ointment => 'Pommade',
            self::Gel => 'Gel',
            self::Drops => 'Gouttes',
            self::Suppository => 'Suppositoire',
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
