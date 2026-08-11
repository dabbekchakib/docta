<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum AlcoholStatus: string implements HasLabel
{
    case Never = 'never';
    case Occasional = 'occasional';
    case Regular = 'regular';
    case Former = 'former';
    case Unknown = 'unknown';

    public function getLabel(): string
    {
        return match ($this) {
            self::Never => 'Jamais',
            self::Occasional => 'Occasionnel',
            self::Regular => 'Régulier',
            self::Former => 'Ancien consommateur',
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
