<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum RelationType: string implements HasLabel
{
    case Spouse = 'spouse';
    case Parent = 'parent';
    case Child = 'child';
    case Sibling = 'sibling';
    case Friend = 'friend';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Spouse => 'Conjoint(e)',
            self::Parent => 'Parent',
            self::Child => 'Enfant',
            self::Sibling => 'Frère / Sœur',
            self::Friend => 'Ami(e)',
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
