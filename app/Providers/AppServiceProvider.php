<?php

namespace App\Providers;

use App\Events\ConsultationCreated;
use App\Listeners\NotifyConsultationDoctor;
use App\Models\Allergy;
use App\Models\Appointment;
use App\Models\ChronicDisease;
use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\FamilyHistory;
use App\Models\MedicalDocument;
use App\Models\MedicalHistory;
use App\Models\MedicalRecord;
use App\Models\Medication;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\Secretary;
use App\Models\SurgicalHistory;
use App\Models\User;
use App\Models\Vaccination;
use App\Models\Laboratory;
use App\Models\LaboratoryReport;
use App\Models\LaboratoryRequest;
use App\Models\LaboratoryResult;
use App\Models\LaboratoryTest;
use App\Models\CreditNote;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Receipt;
use App\Models\Refund;
use App\Models\Service;
use App\Models\TaxRate;
use App\Policies\AllergyPolicy;
use App\Policies\AppointmentPolicy;
use App\Policies\ChronicDiseasePolicy;
use App\Policies\ConsultationPolicy;
use App\Policies\CreditNotePolicy;
use App\Policies\DoctorPolicy;
use App\Policies\FamilyHistoryPolicy;
use App\Policies\InvoicePolicy;
use App\Policies\LaboratoryPolicy;
use App\Policies\LaboratoryReportPolicy;
use App\Policies\LaboratoryRequestPolicy;
use App\Policies\LaboratoryResultPolicy;
use App\Policies\LaboratoryTestPolicy;
use App\Policies\MedicalDocumentPolicy;
use App\Policies\MedicalHistoryPolicy;
use App\Policies\MedicalRecordPolicy;
use App\Policies\MedicationPolicy;
use App\Policies\PatientPolicy;
use App\Policies\PaymentMethodPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\PermissionPolicy;
use App\Policies\PrescriptionPolicy;
use App\Policies\ReceiptPolicy;
use App\Policies\RefundPolicy;
use App\Policies\RolePolicy;
use App\Policies\SecretaryPolicy;
use App\Policies\ServicePolicy;
use App\Policies\SurgicalHistoryPolicy;
use App\Policies\TaxRatePolicy;
use App\Policies\UserPolicy;
use App\Policies\VaccinationPolicy;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Patient::class, PatientPolicy::class);
        Gate::policy(Doctor::class, DoctorPolicy::class);
        Gate::policy(Secretary::class, SecretaryPolicy::class);
        Gate::policy(Appointment::class, AppointmentPolicy::class);
        Gate::policy(Consultation::class, ConsultationPolicy::class);
        Gate::policy(MedicalRecord::class, MedicalRecordPolicy::class);
        Gate::policy(MedicalHistory::class, MedicalHistoryPolicy::class);
        Gate::policy(SurgicalHistory::class, SurgicalHistoryPolicy::class);
        Gate::policy(FamilyHistory::class, FamilyHistoryPolicy::class);
        Gate::policy(Allergy::class, AllergyPolicy::class);
        Gate::policy(ChronicDisease::class, ChronicDiseasePolicy::class);
        Gate::policy(Medication::class, MedicationPolicy::class);
        Gate::policy(Vaccination::class, VaccinationPolicy::class);
        Gate::policy(MedicalDocument::class, MedicalDocumentPolicy::class);
        Gate::policy(Prescription::class, PrescriptionPolicy::class);
        Gate::policy(Laboratory::class, LaboratoryPolicy::class);
        Gate::policy(LaboratoryTest::class, LaboratoryTestPolicy::class);
        Gate::policy(LaboratoryRequest::class, LaboratoryRequestPolicy::class);
        Gate::policy(LaboratoryResult::class, LaboratoryResultPolicy::class);
        Gate::policy(LaboratoryReport::class, LaboratoryReportPolicy::class);
        Gate::policy(Service::class, ServicePolicy::class);
        Gate::policy(TaxRate::class, TaxRatePolicy::class);
        Gate::policy(PaymentMethod::class, PaymentMethodPolicy::class);
        Gate::policy(Invoice::class, InvoicePolicy::class);
        Gate::policy(Payment::class, PaymentPolicy::class);
        Gate::policy(Receipt::class, ReceiptPolicy::class);
        Gate::policy(CreditNote::class, CreditNotePolicy::class);
        Gate::policy(Refund::class, RefundPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Permission::class, PermissionPolicy::class);

        Event::listen(
            ConsultationCreated::class,
            NotifyConsultationDoctor::class,
        );
    }
}
