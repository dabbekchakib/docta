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

    case DoctorsView = 'doctors.view';
    case DoctorsCreate = 'doctors.create';
    case DoctorsUpdate = 'doctors.update';
    case DoctorsDelete = 'doctors.delete';
    case DoctorsExport = 'doctors.export';

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
            self::DoctorsView => 'Voir les médecins',
            self::DoctorsCreate => 'Créer des médecins',
            self::DoctorsUpdate => 'Modifier des médecins',
            self::DoctorsDelete => 'Supprimer des médecins',
            self::DoctorsExport => 'Exporter les médecins',
            self::AppointmentsManage => 'Gérer les rendez-vous',
            self::ConsultationsManage => 'Gérer les consultations',
            self::BillingManage => 'Gérer la facturation',
            self::ReportsView => 'Consulter les rapports',
        };
    }
}
