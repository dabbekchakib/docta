<?php

namespace App\Enums;

enum Role: string
{
    case SuperAdmin = 'super_admin';
    case Admin = 'admin';
    case Doctor = 'doctor';
    case Secretary = 'secretary';
    case Patient = 'patient';
    case Accountant = 'accountant';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Administrateur',
            self::Admin => 'Administrateur',
            self::Doctor => 'Médecin',
            self::Secretary => 'Secrétaire',
            self::Patient => 'Patient',
            self::Accountant => 'Comptable',
        };
    }

    public function canAccessPanel(): bool
    {
        return match ($this) {
            self::Patient => false,
            default => true,
        };
    }
}
