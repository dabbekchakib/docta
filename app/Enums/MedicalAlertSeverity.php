<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum MedicalAlertSeverity: string implements HasColor, HasLabel
{
    case Info = 'info';
    case Warning = 'warning';
    case Danger = 'danger';
    case Critical = 'critical';

    public function getLabel(): string
    {
        return match ($this) {
            self::Info => 'Info',
            self::Warning => 'Attention',
            self::Danger => 'Danger',
            self::Critical => 'Critique',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Info => 'info',
            self::Warning => 'warning',
            self::Danger => 'danger',
            self::Critical => 'rose',
        };
    }

    public function isHighPriority(): bool
    {
        return $this === self::Danger || $this === self::Critical;
    }
}
