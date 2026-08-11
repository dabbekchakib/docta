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
use App\Models\Secretary;
use App\Models\SurgicalHistory;
use App\Models\User;
use App\Models\Vaccination;
use App\Policies\AllergyPolicy;
use App\Policies\AppointmentPolicy;
use App\Policies\ChronicDiseasePolicy;
use App\Policies\ConsultationPolicy;
use App\Policies\DoctorPolicy;
use App\Policies\FamilyHistoryPolicy;
use App\Policies\MedicalDocumentPolicy;
use App\Policies\MedicalHistoryPolicy;
use App\Policies\MedicalRecordPolicy;
use App\Policies\MedicationPolicy;
use App\Policies\PatientPolicy;
use App\Policies\PermissionPolicy;
use App\Policies\RolePolicy;
use App\Policies\SecretaryPolicy;
use App\Policies\SurgicalHistoryPolicy;
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
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Permission::class, PermissionPolicy::class);

        Event::listen(
            ConsultationCreated::class,
            NotifyConsultationDoctor::class,
        );
    }
}
