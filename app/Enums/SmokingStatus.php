<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum SmokingStatus: string implements HasLabel
{
    case Never = 'never';
    case Former = 'former';
    case Current = 'current';
    case Unknown = 'unknown';

    public function getLabel(): string
    {
        return match ($this) {
            self::Never => 'Jamais',
            self::Former => 'Ancien fumeur',
            self::Current => 'Fumeur actuel',
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
