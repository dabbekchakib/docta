<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum LaboratoryRequestStatus: string implements HasColor, HasLabel
{
    case Draft = 'draft';
    case Requested = 'requested';
    case Accepted = 'accepted';
    case SampleCollected = 'sample_collected';
    case InAnalysis = 'in_analysis';
    case ResultsEntered = 'results_entered';
    case Validated = 'validated';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Brouillon',
            self::Requested => 'Demandée',
            self::Accepted => 'Acceptée',
            self::SampleCollected => 'Prélèvement effectué',
            self::InAnalysis => 'En analyse',
            self::ResultsEntered => 'Résultats saisis',
            self::Validated => 'Validée',
            self::Completed => 'Terminée',
            self::Cancelled => 'Annulée',
        };
    }

    public function getLabel(): string
    {
        return $this->label();
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Requested => 'info',
            self::Accepted => 'primary',
            self::SampleCollected => 'purple',
            self::InAnalysis => 'warning',
            self::ResultsEntered => 'warning',
            self::Validated => 'success',
            self::Completed => 'success',
            self::Cancelled => 'danger',
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
