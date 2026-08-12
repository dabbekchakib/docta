<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum LaboratoryRequestPriority: string implements HasColor, HasLabel
{
    case Normal = 'normal';
    case Urgent = 'urgent';
    case Critical = 'critical';

    public function label(): string
    {
        return match ($this) {
            self::Normal => 'Normale',
            self::Urgent => 'Urgente',
            self::Critical => 'Critique',
        };
    }

    public function getLabel(): string
    {
        return $this->label();
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Normal => 'gray',
            self::Urgent => 'warning',
            self::Critical => 'danger',
        };
    }

    /**
     * Niveau de sévérité pour trier les demandes (ordre croissant).
     */
    public function severity(): int
    {
        return match ($this) {
            self::Normal => 0,
            self::Urgent => 1,
            self::Critical => 2,
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
