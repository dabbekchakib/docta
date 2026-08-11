<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ConsultationType: string implements HasColor, HasLabel
{
    case FirstVisit = 'first_visit';
    case Control = 'control';
    case FollowUp = 'follow_up';
    case Emergency = 'emergency';
    case Teleconsultation = 'teleconsultation';

    public function label(): string
    {
        return match ($this) {
            self::FirstVisit => 'Première visite',
            self::Control => 'Contrôle',
            self::FollowUp => 'Suivi',
            self::Emergency => 'Urgence',
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
            self::FirstVisit => 'primary',
            self::Control => 'info',
            self::FollowUp => 'success',
            self::Emergency => 'danger',
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
