<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ResultAbnormality: string implements HasColor, HasLabel
{
    case Normal = 'normal';
    case Low = 'low';
    case High = 'high';
    case CriticalLow = 'critical_low';
    case CriticalHigh = 'critical_high';
    case Positive = 'positive';
    case Negative = 'negative';

    public function label(): string
    {
        return match ($this) {
            self::Normal => 'Normal',
            self::Low => 'Bas',
            self::High => 'Élevé',
            self::CriticalLow => 'Critique bas',
            self::CriticalHigh => 'Critique élevé',
            self::Positive => 'Positif',
            self::Negative => 'Négatif',
        };
    }

    public function getLabel(): string
    {
        return $this->label();
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Normal => 'success',
            self::Low, self::High => 'warning',
            self::CriticalLow, self::CriticalHigh => 'danger',
            self::Positive => 'danger',
            self::Negative => 'success',
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
