<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ChronicDiseaseStatus: string implements HasColor, HasLabel
{
    case Active = 'active';
    case Controlled = 'controlled';
    case Resolved = 'resolved';
    case Unknown = 'unknown';

    public function getLabel(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Controlled => 'Contrôlée',
            self::Resolved => 'Résolue',
            self::Unknown => 'Inconnu',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Active => 'danger',
            self::Controlled => 'warning',
            self::Resolved => 'success',
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
