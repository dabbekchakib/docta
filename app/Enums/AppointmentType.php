<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum AppointmentType: string implements HasColor, HasLabel
{
    case Consultation = 'consultation';
    case Control = 'control';
    case Urgent = 'urgent';
    case FollowUp = 'follow_up';
    case Teleconsultation = 'teleconsultation';

    public function label(): string
    {
        return match ($this) {
            self::Consultation => 'Consultation',
            self::Control => 'Contrôle',
            self::Urgent => 'Urgence',
            self::FollowUp => 'Suivi',
            self::Teleconsultation => 'Téléconsultation',
        };
    }

    public function getLabel(): string
    {
        return $this->label();
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Consultation => 'primary',
            self::Control => 'info',
            self::Urgent => 'danger',
            self::FollowUp => 'success',
            self::Teleconsultation => 'gray',
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
