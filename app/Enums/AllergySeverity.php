<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum AllergySeverity: string implements HasColor, HasLabel
{
    case Mild = 'mild';
    case Moderate = 'moderate';
    case Severe = 'severe';
    case Critical = 'critical';

    public function getLabel(): string
    {
        return match ($this) {
            self::Mild => 'Légère',
            self::Moderate => 'Modérée',
            self::Severe => 'Sévère',
            self::Critical => 'Critique',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Mild => 'info',
            self::Moderate => 'warning',
            self::Severe => 'danger',
            self::Critical => 'rose',
        };
    }

    /**
     * Sévérité considérée comme une alerte à forte visibilité.
     */
    public function isCritical(): bool
    {
        return $this === self::Severe || $this === self::Critical;
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
