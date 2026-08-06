<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum AppointmentStatus: string implements HasColor, HasLabel
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Waiting = 'waiting';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Absent = 'absent';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'En attente',
            self::Confirmed => 'Confirmé',
            self::Waiting => 'En salle d\'attente',
            self::InProgress => 'En cours',
            self::Completed => 'Terminé',
            self::Cancelled => 'Annulé',
            self::Absent => 'Absent',
        };
    }

    public function getLabel(): string
    {
        return $this->label();
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Pending => 'gray',
            self::Confirmed => 'success',
            self::Waiting => 'warning',
            self::InProgress => 'info',
            self::Completed => 'primary',
            self::Cancelled => 'danger',
            self::Absent => 'purple',
        };
    }

    /**
     * Couleur hexadécimale utilisée par la vue calendrier.
     */
    public function calendarColor(): string
    {
        return match ($this) {
            self::Pending => '#6b7280',
            self::Confirmed => '#10b981',
            self::Waiting => '#f59e0b',
            self::InProgress => '#3b82f6',
            self::Completed => '#0284c7',
            self::Cancelled => '#ef4444',
            self::Absent => '#8b5cf6',
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
