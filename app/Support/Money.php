<?php

namespace App\Support;

/**
 * Aide arithmétique monétaire en précision décimale (devise TND, 3 décimales).
 *
 * Toutes les opérations sont réalisées en chaînes via BCMath afin d'éviter
 * les erreurs d'arrondi des flottants sur les montants.
 */
final class Money
{
    public const SCALE = 3;

    public static function add(string|int|float $a, string|int|float $b): string
    {
        return self::normalize(bcadd((string) $a, (string) $b, self::SCALE));
    }

    public static function sub(string|int|float $a, string|int|float $b): string
    {
        return self::normalize(bcsub((string) $a, (string) $b, self::SCALE));
    }

    public static function mul(string|int|float $a, string|int|float $b, int $scale = self::SCALE): string
    {
        return self::normalize(bcmul((string) $a, (string) $b, $scale));
    }

    public static function div(string|int|float $a, string|int|float $b, int $scale = self::SCALE): string
    {
        return self::normalize(bcdiv((string) $a, (string) $b, $scale));
    }

    public static function compare(string|int|float $a, string|int|float $b): int
    {
        return bccomp((string) $a, (string) $b, self::SCALE);
    }

    public static function gt(string|int|float $a, string|int|float $b): bool
    {
        return self::compare($a, $b) > 0;
    }

    public static function gte(string|int|float $a, string|int|float $b): bool
    {
        return self::compare($a, $b) >= 0;
    }

    public static function lt(string|int|float $a, string|int|float $b): bool
    {
        return self::compare($a, $b) < 0;
    }

    public static function lte(string|int|float $a, string|int|float $b): bool
    {
        return self::compare($a, $b) <= 0;
    }

    public static function zero(): string
    {
        return '0.000';
    }

    /**
     * Normalise une valeur décimale (élimine le signe moins nul et les zéros superflus).
     */
    public static function normalize(string $value): string
    {
        if (bccomp($value, '0', self::SCALE) === 0) {
            return self::zero();
        }

        return rtrim(rtrim(number_format((float) $value, self::SCALE, '.', ''), '0'), '.');
    }

    /**
     * Formate un montant pour l'affichage en TND (ex. 150,000 DT).
     */
    public static function format(string|int|float $value): string
    {
        return number_format((float) $value, 3, ',', ' ').' DT';
    }

    /**
     * Arrondi à 2 décimales (utilisé pour les taux de taxe / pourcentages).
     */
    public static function round2(string|int|float $value): string
    {
        return rtrim(rtrim(bcadd((string) $value, '0', 2), '0'), '.');
    }
}
