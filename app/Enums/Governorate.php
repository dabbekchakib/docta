<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Governorate: string implements HasLabel
{
    case Ariana = 'Ariana';
    case Beja = 'Béja';
    case BenArous = 'Ben Arous';
    case Bizerte = 'Bizerte';
    case Gabes = 'Gabès';
    case Gafsa = 'Gafsa';
    case Jendouba = 'Jendouba';
    case Kairouan = 'Kairouan';
    case Kasserine = 'Kasserine';
    case Kebili = 'Kébili';
    case LeKef = 'Le Kef';
    case Mahdia = 'Mahdia';
    case Manouba = 'La Manouba';
    case Medenine = 'Médenine';
    case Monastir = 'Monastir';
    case Nabeul = 'Nabeul';
    case Sfax = 'Sfax';
    case SidiBouzid = 'Sidi Bouzid';
    case Siliana = 'Siliana';
    case Sousse = 'Sousse';
    case Tataouine = 'Tataouine';
    case Tozeur = 'Tozeur';
    case Tunis = 'Tunis';
    case Zaghouan = 'Zaghouan';

    public function getLabel(): string
    {
        return $this->value;
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case): array => [$case->value => $case->value])
            ->all();
    }
}
