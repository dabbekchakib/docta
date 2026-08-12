<?php

namespace Database\Seeders;

use App\Enums\Permission as PermissionEnum;
use App\Enums\Role as RoleEnum;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (PermissionEnum::cases() as $permission) {
            Permission::findOrCreate($permission->value);
        }

        $this->createRole(RoleEnum::SuperAdmin, PermissionEnum::cases());
        $this->createRole(RoleEnum::Admin, [
            PermissionEnum::UsersView,
            PermissionEnum::UsersCreate,
            PermissionEnum::UsersUpdate,
            PermissionEnum::PatientsView,
            PermissionEnum::PatientsCreate,
            PermissionEnum::PatientsUpdate,
            PermissionEnum::PatientsDelete,
            PermissionEnum::DoctorsView,
            PermissionEnum::DoctorsCreate,
            PermissionEnum::DoctorsUpdate,
            PermissionEnum::DoctorsDelete,
            PermissionEnum::DoctorsExport,
            PermissionEnum::SecretariesView,
            PermissionEnum::SecretariesCreate,
            PermissionEnum::SecretariesUpdate,
            PermissionEnum::SecretariesDelete,
            PermissionEnum::SecretariesExport,
            PermissionEnum::AppointmentsView,
            PermissionEnum::AppointmentsCreate,
            PermissionEnum::AppointmentsUpdate,
            PermissionEnum::AppointmentsDelete,
            PermissionEnum::AppointmentsConfirm,
            PermissionEnum::AppointmentsCancel,
            PermissionEnum::AppointmentsCalendar,
            PermissionEnum::ConsultationsView,
            PermissionEnum::ConsultationsCreate,
            PermissionEnum::ConsultationsUpdate,
            PermissionEnum::ConsultationsDelete,
            PermissionEnum::ConsultationsPrint,
            PermissionEnum::MedicalRecordsView,
            PermissionEnum::MedicalRecordsCreate,
            PermissionEnum::MedicalRecordsUpdate,
            PermissionEnum::MedicalHistoriesManage,
            PermissionEnum::AllergiesManage,
            PermissionEnum::ChronicDiseasesManage,
            PermissionEnum::SurgicalHistoriesManage,
            PermissionEnum::FamilyHistoriesManage,
            PermissionEnum::MedicationsManage,
            PermissionEnum::VaccinationsManage,
            PermissionEnum::MedicalDocumentsView,
            PermissionEnum::MedicalDocumentsCreate,
            PermissionEnum::MedicalDocumentsDelete,
            PermissionEnum::MedicalDocumentsDownload,
            PermissionEnum::PrescriptionsView,
            PermissionEnum::PrescriptionsCreate,
            PermissionEnum::PrescriptionsUpdate,
            PermissionEnum::PrescriptionsDelete,
            PermissionEnum::PrescriptionsIssue,
            PermissionEnum::PrescriptionsCancel,
            PermissionEnum::PrescriptionsPrint,
            PermissionEnum::PrescriptionsExport,
            PermissionEnum::LaboratoriesView,
            PermissionEnum::LaboratoriesCreate,
            PermissionEnum::LaboratoriesUpdate,
            PermissionEnum::LaboratoriesDelete,
            PermissionEnum::LaboratoryTestsView,
            PermissionEnum::LaboratoryTestsCreate,
            PermissionEnum::LaboratoryTestsUpdate,
            PermissionEnum::LaboratoryTestsDelete,
            PermissionEnum::LaboratoryRequestsView,
            PermissionEnum::LaboratoryRequestsCreate,
            PermissionEnum::LaboratoryRequestsUpdate,
            PermissionEnum::LaboratoryRequestsCancel,
            PermissionEnum::LaboratoryResultsView,
            PermissionEnum::LaboratoryResultsCreate,
            PermissionEnum::LaboratoryResultsUpdate,
            PermissionEnum::LaboratoryResultsValidate,
            PermissionEnum::LaboratoryReportsView,
            PermissionEnum::LaboratoryReportsCreate,
            PermissionEnum::LaboratoryReportsDownload,
            PermissionEnum::ServicesView,
            PermissionEnum::ServicesCreate,
            PermissionEnum::ServicesUpdate,
            PermissionEnum::ServicesDelete,
            PermissionEnum::TaxRatesView,
            PermissionEnum::TaxRatesManage,
            PermissionEnum::PaymentMethodsView,
            PermissionEnum::PaymentMethodsManage,
            PermissionEnum::InvoicesView,
            PermissionEnum::InvoicesCreate,
            PermissionEnum::InvoicesUpdate,
            PermissionEnum::InvoicesIssue,
            PermissionEnum::InvoicesCancel,
            PermissionEnum::InvoicesDownload,
            PermissionEnum::InvoicesExport,
            PermissionEnum::PaymentsView,
            PermissionEnum::PaymentsCreate,
            PermissionEnum::PaymentsCancel,
            PermissionEnum::PaymentsRefund,
            PermissionEnum::ReceiptsView,
            PermissionEnum::ReceiptsCreate,
            PermissionEnum::ReceiptsDownload,
            PermissionEnum::CreditNotesView,
            PermissionEnum::CreditNotesCreate,
            PermissionEnum::CreditNotesCancel,
            PermissionEnum::RefundsView,
            PermissionEnum::RefundsCreate,
            PermissionEnum::RefundsApprove,
            PermissionEnum::RefundsReject,
            PermissionEnum::FinancialReportsView,
            PermissionEnum::FinancialReportsExport,
            PermissionEnum::CashRegisterView,
            PermissionEnum::ReportsView,
        ]);
        $this->createRole(RoleEnum::Doctor, [
            PermissionEnum::PatientsView,
            PermissionEnum::DoctorsView,
            PermissionEnum::AppointmentsView,
            PermissionEnum::AppointmentsCreate,
            PermissionEnum::AppointmentsUpdate,
            PermissionEnum::AppointmentsConfirm,
            PermissionEnum::AppointmentsCancel,
            PermissionEnum::AppointmentsCalendar,
            PermissionEnum::ConsultationsView,
            PermissionEnum::ConsultationsCreate,
            PermissionEnum::ConsultationsUpdate,
            PermissionEnum::ConsultationsPrint,
            PermissionEnum::MedicalRecordsView,
            PermissionEnum::MedicalRecordsUpdate,
            PermissionEnum::MedicalHistoriesManage,
            PermissionEnum::AllergiesManage,
            PermissionEnum::ChronicDiseasesManage,
            PermissionEnum::SurgicalHistoriesManage,
            PermissionEnum::FamilyHistoriesManage,
            PermissionEnum::MedicationsManage,
            PermissionEnum::VaccinationsManage,
            PermissionEnum::MedicalDocumentsView,
            PermissionEnum::MedicalDocumentsCreate,
            PermissionEnum::MedicalDocumentsDownload,
            PermissionEnum::PrescriptionsView,
            PermissionEnum::PrescriptionsCreate,
            PermissionEnum::PrescriptionsUpdate,
            PermissionEnum::PrescriptionsIssue,
            PermissionEnum::PrescriptionsPrint,
            PermissionEnum::LaboratoriesView,
            PermissionEnum::LaboratoryTestsView,
            PermissionEnum::LaboratoryRequestsView,
            PermissionEnum::LaboratoryRequestsCreate,
            PermissionEnum::LaboratoryRequestsUpdate,
            PermissionEnum::LaboratoryRequestsCancel,
            PermissionEnum::LaboratoryResultsView,
            PermissionEnum::LaboratoryReportsView,
            PermissionEnum::LaboratoryReportsDownload,
            PermissionEnum::ServicesView,
            PermissionEnum::InvoicesView,
            PermissionEnum::InvoicesDownload,
            PermissionEnum::PaymentsView,
            PermissionEnum::ReceiptsView,
            PermissionEnum::ReceiptsDownload,
            PermissionEnum::CreditNotesView,
        ]);
        $this->createRole(RoleEnum::Secretary, [
            PermissionEnum::PatientsView,
            PermissionEnum::PatientsCreate,
            PermissionEnum::PatientsUpdate,
            PermissionEnum::DoctorsView,
            PermissionEnum::AppointmentsView,
            PermissionEnum::AppointmentsCreate,
            PermissionEnum::AppointmentsUpdate,
            PermissionEnum::AppointmentsDelete,
            PermissionEnum::AppointmentsConfirm,
            PermissionEnum::AppointmentsCancel,
            PermissionEnum::AppointmentsCalendar,
            PermissionEnum::ConsultationsView,
            PermissionEnum::PrescriptionsView,
            PermissionEnum::PrescriptionsPrint,
            PermissionEnum::LaboratoriesView,
            PermissionEnum::LaboratoryTestsView,
            PermissionEnum::LaboratoryRequestsView,
            PermissionEnum::LaboratoryRequestsCreate,
            PermissionEnum::LaboratoryRequestsUpdate,
            PermissionEnum::LaboratoryRequestsCancel,
            PermissionEnum::ServicesView,
            PermissionEnum::InvoicesView,
            PermissionEnum::InvoicesCreate,
            PermissionEnum::InvoicesUpdate,
            PermissionEnum::InvoicesIssue,
            PermissionEnum::InvoicesDownload,
            PermissionEnum::PaymentsView,
            PermissionEnum::PaymentsCreate,
            PermissionEnum::PaymentsCancel,
            PermissionEnum::ReceiptsView,
            PermissionEnum::ReceiptsCreate,
            PermissionEnum::ReceiptsDownload,
            PermissionEnum::CreditNotesView,
            PermissionEnum::CreditNotesCreate,
            PermissionEnum::FinancialReportsView,
        ]);
        $this->createRole(RoleEnum::Patient, [
            PermissionEnum::PatientsView,
        ]);
        $this->createRole(RoleEnum::Accountant, [
            PermissionEnum::PatientsView,
            PermissionEnum::BillingManage,
            PermissionEnum::ReportsView,
            PermissionEnum::ServicesView,
            PermissionEnum::TaxRatesView,
            PermissionEnum::PaymentMethodsView,
            PermissionEnum::InvoicesView,
            PermissionEnum::InvoicesDownload,
            PermissionEnum::InvoicesExport,
            PermissionEnum::PaymentsView,
            PermissionEnum::ReceiptsView,
            PermissionEnum::ReceiptsDownload,
            PermissionEnum::CreditNotesView,
            PermissionEnum::RefundsView,
            PermissionEnum::RefundsCreate,
            PermissionEnum::RefundsApprove,
            PermissionEnum::RefundsReject,
            PermissionEnum::FinancialReportsView,
            PermissionEnum::FinancialReportsExport,
            PermissionEnum::CashRegisterView,
        ]);

        $this->createAdminUsers();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * @param  PermissionEnum[]  $permissions
     */
    private function createRole(RoleEnum $role, array $permissions): void
    {
        $roleModel = Role::findOrCreate($role->value);

        $roleModel->syncPermissions(
            array_map(fn (PermissionEnum $permission) => $permission->value, $permissions)
        );
    }

    private function createAdminUsers(): void
    {
        $superAdmin = User::updateOrCreate(
            ['email' => 'admin@docta.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $superAdmin->syncRoles([RoleEnum::SuperAdmin->value]);
    }
}
