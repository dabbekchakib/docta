<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum MedicationStatus: string implements HasColor, HasLabel
{
    case Active = 'active';
    case Stopped = 'stopped';
    case Unknown = 'unknown';

    public function getLabel(): string
    {
        return match ($this) {
            self::Active => 'En cours',
            self::Stopped => 'Arrêté',
            self::Unknown => 'Inconnu',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Active => 'success',
            self::Stopped => 'gray',
            self::Unknown => 'gray',
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
