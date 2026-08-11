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

    case SecretariesView = 'secretaries.view';
    case SecretariesCreate = 'secretaries.create';
    case SecretariesUpdate = 'secretaries.update';
    case SecretariesDelete = 'secretaries.delete';
    case SecretariesExport = 'secretaries.export';

    case AppointmentsManage = 'appointments.manage';
    case AppointmentsView = 'appointments.view';
    case AppointmentsCreate = 'appointments.create';
    case AppointmentsUpdate = 'appointments.update';
    case AppointmentsDelete = 'appointments.delete';
    case AppointmentsConfirm = 'appointments.confirm';
    case AppointmentsCancel = 'appointments.cancel';
    case AppointmentsCalendar = 'appointments.calendar';

    case ConsultationsManage = 'consultations.manage';
    case ConsultationsView = 'consultations.view';
    case ConsultationsCreate = 'consultations.create';
    case ConsultationsUpdate = 'consultations.update';
    case ConsultationsDelete = 'consultations.delete';
    case ConsultationsPrint = 'consultations.print';

    case MedicalRecordsView = 'medical_records.view';
    case MedicalRecordsCreate = 'medical_records.create';
    case MedicalRecordsUpdate = 'medical_records.update';
    case MedicalRecordsDelete = 'medical_records.delete';
    case MedicalHistoriesManage = 'medical_histories.manage';
    case AllergiesManage = 'allergies.manage';
    case ChronicDiseasesManage = 'chronic_diseases.manage';
    case SurgicalHistoriesManage = 'surgical_histories.manage';
    case FamilyHistoriesManage = 'family_histories.manage';
    case MedicationsManage = 'medications.manage';
    case VaccinationsManage = 'vaccinations.manage';
    case MedicalDocumentsView = 'medical_documents.view';
    case MedicalDocumentsCreate = 'medical_documents.create';
    case MedicalDocumentsDelete = 'medical_documents.delete';
    case MedicalDocumentsDownload = 'medical_documents.download';

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
            self::SecretariesView => 'Voir les secrétaires',
            self::SecretariesCreate => 'Créer des secrétaires',
            self::SecretariesUpdate => 'Modifier des secrétaires',
            self::SecretariesDelete => 'Supprimer des secrétaires',
            self::SecretariesExport => 'Exporter les secrétaires',
            self::AppointmentsManage => 'Gérer les rendez-vous',
            self::AppointmentsView => 'Voir les rendez-vous',
            self::AppointmentsCreate => 'Créer des rendez-vous',
            self::AppointmentsUpdate => 'Modifier des rendez-vous',
            self::AppointmentsDelete => 'Supprimer des rendez-vous',
            self::AppointmentsConfirm => 'Confirmer des rendez-vous',
            self::AppointmentsCancel => 'Annuler des rendez-vous',
            self::AppointmentsCalendar => 'Consulter l\'agenda',
            self::ConsultationsManage => 'Gérer les consultations',
            self::ConsultationsView => 'Voir les consultations',
            self::ConsultationsCreate => 'Créer des consultations',
            self::ConsultationsUpdate => 'Modifier des consultations',
            self::ConsultationsDelete => 'Supprimer des consultations',
            self::ConsultationsPrint => 'Imprimer des consultations',
            self::MedicalRecordsView => 'Voir les dossiers médicaux',
            self::MedicalRecordsCreate => 'Créer des dossiers médicaux',
            self::MedicalRecordsUpdate => 'Modifier des dossiers médicaux',
            self::MedicalRecordsDelete => 'Supprimer des dossiers médicaux',
            self::MedicalHistoriesManage => 'Gérer les antécédents médicaux',
            self::AllergiesManage => 'Gérer les allergies',
            self::ChronicDiseasesManage => 'Gérer les maladies chroniques',
            self::SurgicalHistoriesManage => 'Gérer les antécédents chirurgicaux',
            self::FamilyHistoriesManage => 'Gérer les antécédents familiaux',
            self::MedicationsManage => 'Gérer les traitements permanents',
            self::VaccinationsManage => 'Gérer les vaccinations',
            self::MedicalDocumentsView => 'Voir les documents médicaux',
            self::MedicalDocumentsCreate => 'Ajouter des documents médicaux',
            self::MedicalDocumentsDelete => 'Supprimer des documents médicaux',
            self::MedicalDocumentsDownload => 'Télécharger des documents médicaux',
            self::BillingManage => 'Gérer la facturation',
            self::ReportsView => 'Consulter les rapports',
        };
    }
}
