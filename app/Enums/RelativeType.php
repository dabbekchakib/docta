<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum RelativeType: string implements HasLabel
{
    case Father = 'pere';
    case Mother = 'mere';
    case Brother = 'frere';
    case Sister = 'soeur';
    case GrandParent = 'grand_parent';
    case Other = 'autre';

    public function getLabel(): string
    {
        return match ($this) {
            self::Father => 'Père',
            self::Mother => 'Mère',
            self::Brother => 'Frère',
            self::Sister => 'Sœur',
            self::GrandParent => 'Grand-parent',
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
