<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum SampleType: string implements HasColor, HasLabel
{
    case Blood = 'blood';
    case Urine = 'urine';
    case Stool = 'stool';
    case Saliva = 'saliva';
    case Swab = 'swab';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Blood => 'Sang',
            self::Urine => 'Urines',
            self::Stool => 'Selles',
            self::Saliva => 'Salive',
            self::Swab => 'Prélèvement',
            self::Other => 'Autre',
        };
    }

    public function getLabel(): string
    {
        return $this->label();
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Blood => 'danger',
            self::Urine => 'warning',
            self::Stool => 'gray',
            self::Saliva => 'info',
            self::Swab => 'purple',
            self::Other => 'gray',
        };
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
