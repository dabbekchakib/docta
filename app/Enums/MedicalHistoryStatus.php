<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum MedicalHistoryStatus: string implements HasColor, HasLabel
{
    case Active = 'active';
    case Resolved = 'resolved';
    case Unknown = 'unknown';

    public function getLabel(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Resolved => 'Résolue',
            self::Unknown => 'Inconnu',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Active => 'danger',
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
