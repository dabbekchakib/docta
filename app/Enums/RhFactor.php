<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum RhFactor: string implements HasLabel
{
    case Positive = '+';
    case Negative = '-';
    case Unknown = 'unknown';

    public function getLabel(): string
    {
        return match ($this) {
            self::Positive => 'Rh+',
            self::Negative => 'Rh-',
            self::Unknown => 'Inconnu',
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
