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

    case PrescriptionsView = 'prescriptions.view';
    case PrescriptionsCreate = 'prescriptions.create';
    case PrescriptionsUpdate = 'prescriptions.update';
    case PrescriptionsDelete = 'prescriptions.delete';
    case PrescriptionsIssue = 'prescriptions.issue';
    case PrescriptionsCancel = 'prescriptions.cancel';
    case PrescriptionsPrint = 'prescriptions.print';
    case PrescriptionsExport = 'prescriptions.export';

    case LaboratoriesView = 'laboratories.view';
    case LaboratoriesCreate = 'laboratories.create';
    case LaboratoriesUpdate = 'laboratories.update';
    case LaboratoriesDelete = 'laboratories.delete';

    case LaboratoryTestsView = 'laboratory_tests.view';
    case LaboratoryTestsCreate = 'laboratory_tests.create';
    case LaboratoryTestsUpdate = 'laboratory_tests.update';
    case LaboratoryTestsDelete = 'laboratory_tests.delete';

    case LaboratoryRequestsView = 'laboratory_requests.view';
    case LaboratoryRequestsCreate = 'laboratory_requests.create';
    case LaboratoryRequestsUpdate = 'laboratory_requests.update';
    case LaboratoryRequestsCancel = 'laboratory_requests.cancel';

    case LaboratoryResultsView = 'laboratory_results.view';
    case LaboratoryResultsCreate = 'laboratory_results.create';
    case LaboratoryResultsUpdate = 'laboratory_results.update';
    case LaboratoryResultsValidate = 'laboratory_results.validate';

    case LaboratoryReportsView = 'laboratory_reports.view';
    case LaboratoryReportsCreate = 'laboratory_reports.create';
    case LaboratoryReportsDownload = 'laboratory_reports.download';

    case ServicesView = 'services.view';
    case ServicesCreate = 'services.create';
    case ServicesUpdate = 'services.update';
    case ServicesDelete = 'services.delete';

    case TaxRatesView = 'tax_rates.view';
    case TaxRatesManage = 'tax_rates.manage';

    case PaymentMethodsView = 'payment_methods.view';
    case PaymentMethodsManage = 'payment_methods.manage';

    case InvoicesView = 'invoices.view';
    case InvoicesCreate = 'invoices.create';
    case InvoicesUpdate = 'invoices.update';
    case InvoicesIssue = 'invoices.issue';
    case InvoicesCancel = 'invoices.cancel';
    case InvoicesDownload = 'invoices.download';
    case InvoicesExport = 'invoices.export';

    case PaymentsView = 'payments.view';
    case PaymentsCreate = 'payments.create';
    case PaymentsUpdate = 'payments.update';
    case PaymentsValidate = 'payments.validate';
    case PaymentsCancel = 'payments.cancel';
    case PaymentsRefund = 'payments.refund';

    case ReceiptsView = 'receipts.view';
    case ReceiptsCreate = 'receipts.create';
    case ReceiptsDownload = 'receipts.download';

    case CreditNotesView = 'credit_notes.view';
    case CreditNotesCreate = 'credit_notes.create';
    case CreditNotesCancel = 'credit_notes.cancel';

    case RefundsView = 'refunds.view';
    case RefundsCreate = 'refunds.create';
    case RefundsApprove = 'refunds.approve';
    case RefundsReject = 'refunds.reject';

    case FinancialReportsView = 'financial_reports.view';
    case FinancialReportsExport = 'financial_reports.export';

    case CashRegisterView = 'cash_register.view';

    case AccountingView = 'accounting.view';
    case AccountingCreate = 'accounting.create';
    case AccountingCancel = 'accounting.cancel';
    case AccountingAccountsManage = 'accounting.accounts.manage';

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
            self::PrescriptionsView => 'Voir les ordonnances',
            self::PrescriptionsCreate => 'Créer des ordonnances',
            self::PrescriptionsUpdate => 'Modifier des ordonnances',
            self::PrescriptionsDelete => 'Supprimer des ordonnances',
            self::PrescriptionsIssue => 'Émettre des ordonnances',
            self::PrescriptionsCancel => 'Annuler des ordonnances',
            self::PrescriptionsPrint => 'Imprimer des ordonnances',
            self::PrescriptionsExport => 'Exporter des ordonnances',
            self::LaboratoriesView => 'Voir les laboratoires',
            self::LaboratoriesCreate => 'Créer des laboratoires',
            self::LaboratoriesUpdate => 'Modifier des laboratoires',
            self::LaboratoriesDelete => 'Supprimer des laboratoires',
            self::LaboratoryTestsView => 'Voir les examens de laboratoire',
            self::LaboratoryTestsCreate => 'Créer des examens de laboratoire',
            self::LaboratoryTestsUpdate => 'Modifier des examens de laboratoire',
            self::LaboratoryTestsDelete => 'Supprimer des examens de laboratoire',
            self::LaboratoryRequestsView => 'Voir les demandes d\'examens',
            self::LaboratoryRequestsCreate => 'Créer des demandes d\'examens',
            self::LaboratoryRequestsUpdate => 'Modifier des demandes d\'examens',
            self::LaboratoryRequestsCancel => 'Annuler des demandes d\'examens',
            self::LaboratoryResultsView => 'Voir les résultats de laboratoire',
            self::LaboratoryResultsCreate => 'Saisir des résultats de laboratoire',
            self::LaboratoryResultsUpdate => 'Modifier des résultats de laboratoire',
            self::LaboratoryResultsValidate => 'Valider biologiquement les résultats',
            self::LaboratoryReportsView => 'Voir les comptes rendus de laboratoire',
            self::LaboratoryReportsCreate => 'Générer des comptes rendus de laboratoire',
            self::LaboratoryReportsDownload => 'Télécharger les comptes rendus de laboratoire',
            self::ServicesView => 'Voir les prestations',
            self::ServicesCreate => 'Créer des prestations',
            self::ServicesUpdate => 'Modifier des prestations',
            self::ServicesDelete => 'Supprimer des prestations',
            self::TaxRatesView => 'Voir les taux de taxe',
            self::TaxRatesManage => 'Gérer les taux de taxe',
            self::PaymentMethodsView => 'Voir les moyens de paiement',
            self::PaymentMethodsManage => 'Gérer les moyens de paiement',
            self::InvoicesView => 'Voir les factures',
            self::InvoicesCreate => 'Créer des factures',
            self::InvoicesUpdate => 'Modifier des factures',
            self::InvoicesIssue => 'Émettre des factures',
            self::InvoicesCancel => 'Annuler des factures',
            self::InvoicesDownload => 'Télécharger des factures',
            self::InvoicesExport => 'Exporter des factures',
            self::PaymentsView => 'Voir les paiements',
            self::PaymentsCreate => 'Encaisser des paiements',
            self::PaymentsUpdate => 'Modifier des paiements',
            self::PaymentsValidate => 'Valider des paiements',
            self::PaymentsCancel => 'Annuler des paiements',
            self::PaymentsRefund => 'Rembourser des paiements',
            self::ReceiptsView => 'Voir les reçus',
            self::ReceiptsCreate => 'Créer des reçus',
            self::ReceiptsDownload => 'Télécharger des reçus',
            self::CreditNotesView => 'Voir les avoirs',
            self::CreditNotesCreate => 'Créer des avoirs',
            self::CreditNotesCancel => 'Annuler des avoirs',
            self::RefundsView => 'Voir les remboursements',
            self::RefundsCreate => 'Créer des remboursements',
            self::RefundsApprove => 'Approuver des remboursements',
            self::RefundsReject => 'Refuser des remboursements',
            self::FinancialReportsView => 'Voir les rapports financiers',
            self::FinancialReportsExport => 'Exporter les rapports financiers',
            self::CashRegisterView => 'Consulter la caisse',
            self::AccountingView => 'Voir la comptabilité',
            self::AccountingCreate => 'Saisir des écritures comptables',
            self::AccountingCancel => 'Annuler des écritures comptables',
            self::AccountingAccountsManage => 'Gérer le plan comptable',
            self::BillingManage => 'Gérer la facturation',
            self::ReportsView => 'Consulter les rapports',
        };
    }
}
