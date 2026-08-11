<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum DurationUnit: string implements HasLabel
{
    case Day = 'jour';
    case Week = 'semaine';
    case Month = 'mois';

    public function label(): string
    {
        return match ($this) {
            self::Day => 'Jour',
            self::Week => 'Semaine',
            self::Month => 'Mois',
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
