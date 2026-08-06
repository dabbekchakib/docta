<?php

namespace App\Enums;

enum Permission: string
{
    case UsersView = 'users.view';
    case UsersCreate = 'users.create';
    case UsersUpdate = 'users.update';
    case UsersDelete = 'users.delete';

    case PatientsView = 'patients.view';
    case PatientsCreate = 'patients.create';
    case PatientsUpdate = 'patients.update';
    case PatientsDelete = 'patients.delete';

    case AppointmentsManage = 'appointments.manage';

    case ConsultationsManage = 'consultations.manage';

    case BillingManage = 'billing.manage';

    case ReportsView = 'reports.view';

    public function label(): string
    {
        return match ($this) {
            self::UsersView => 'Voir les utilisateurs',
            self::UsersCreate => 'Créer des utilisateurs',
            self::UsersUpdate => 'Modifier des utilisateurs',
            self::UsersDelete => 'Supprimer des utilisateurs',
            self::PatientsView => 'Voir les patients',
            self::PatientsCreate => 'Créer des patients',
            self::PatientsUpdate => 'Modifier des patients',
            self::PatientsDelete => 'Supprimer des patients',
            self::AppointmentsManage => 'Gérer les rendez-vous',
            self::ConsultationsManage => 'Gérer les consultations',
            self::BillingManage => 'Gérer la facturation',
            self::ReportsView => 'Consulter les rapports',
        };
    }
}
