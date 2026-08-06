<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PatientTitle: string implements HasLabel
{
    case Mr = 'mr';
    case Mrs = 'mme';
    case Dr = 'dr';

    public function label(): string
    {
        return match ($this) {
            self::Mr => 'Monsieur',
            self::Mrs => 'Madame',
            self::Dr => 'Docteur',
        };
    }

    public function getLabel(): string
    {
        return $this->label();
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
