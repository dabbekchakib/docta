<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PaymentMethodType: string implements HasColor, HasLabel
{
    case Cash = 'cash';
    case Card = 'card';
    case Check = 'check';
    case Transfer = 'transfer';
    case Cnam = 'cnam';
    case Insurance = 'insurance';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Espèces',
            self::Card => 'Carte bancaire',
            self::Check => 'Chèque',
            self::Transfer => 'Virement',
            self::Cnam => 'CNAM',
            self::Insurance => 'Assurance',
            self::Other => 'Autre',
        };
    }

    public function getLabel(): string
    {
        return $this->label();
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Cash => 'success',
            self::Card => 'primary',
            self::Check => 'warning',
            self::Transfer => 'info',
            self::Cnam => 'gray',
            self::Insurance => 'gray',
            self::Other => 'gray',
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
