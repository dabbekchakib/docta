<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PrescriptionStatus: string implements HasColor, HasLabel
{
    case Draft = 'draft';
    case Issued = 'issued';
    case Cancelled = 'cancelled';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Brouillon',
            self::Issued => 'Émise',
            self::Cancelled => 'Annulée',
            self::Expired => 'Expirée',
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
            self::Issued => 'success',
            self::Cancelled => 'danger',
            self::Expired => 'warning',
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
