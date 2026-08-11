<?php

namespace Tests\Feature;

use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MedicalRecordPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    protected function doctorUser(): array
    {
        $user = User::factory()->create();
        $user->assignRole('doctor');
        $doctor = Doctor::factory()->create(['user_id' => $user->id]);

        return [$user, $doctor];
    }

    protected function recordForDoctor(array $doctor): array
    {
        $patient = Patient::factory()->create();
        Consultation::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor['id'],
            'consultation_date' => now()->toDateString(),
        ]);

        return [$patient, $patient->medicalRecord];
    }

    public function test_super_admin_can_manage_all_medical_records(): void
    {
        $superAdmin = User::where('email', 'admin@docta.com')->firstOrFail();
        $record = Patient::factory()->create()->medicalRecord;

        $this->assertTrue($superAdmin->can('viewAny', MedicalRecord::class));
        $this->assertTrue($superAdmin->can('view', $record));
        $this->assertTrue($superAdmin->can('update', $record));
        $this->assertTrue($superAdmin->can('delete', $record));
        $this->assertTrue($superAdmin->can('export', $record));
    }

    public function test_admin_can_view_update_and_export_but_not_delete(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $record = Patient::factory()->create()->medicalRecord;

        $this->assertTrue($admin->can('viewAny', MedicalRecord::class));
        $this->assertTrue($admin->can('view', $record));
        $this->assertTrue($admin->can('update', $record));
        $this->assertTrue($admin->can('export', $record));
        $this->assertFalse($admin->can('delete', $record));
    }

    public function test_doctor_can_access_only_his_patients_records(): void
    {
        [$doctorUser, $doctor] = $this->doctorUser();
        [$patient, $own] = $this->recordForDoctor(['id' => $doctor->id]);
        $other = Patient::factory()->create()->medicalRecord;

        $this->assertTrue($doctorUser->can('viewAny', MedicalRecord::class));
        $this->assertTrue($doctorUser->can('view', $own));
        $this->assertTrue($doctorUser->can('update', $own));
        $this->assertTrue($doctorUser->can('export', $own));
        $this->assertFalse($doctorUser->can('view', $other));
        $this->assertFalse($doctorUser->can('update', $other));
        $this->assertFalse($doctorUser->can('export', $other));
    }

    public function test_doctor_cannot_delete_medical_records(): void
    {
        [$doctorUser, $doctor] = $this->doctorUser();
        [, $own] = $this->recordForDoctor(['id' => $doctor->id]);

        $this->assertFalse($doctorUser->can('delete', $own));
        $this->assertFalse($doctorUser->can('deleteAny', MedicalRecord::class));
    }

    public function test_secretary_has_no_access_to_medical_records(): void
    {
        $secretary = User::factory()->create();
        $secretary->assignRole('secretary');
        $record = Patient::factory()->create()->medicalRecord;

        $this->assertFalse($secretary->can('viewAny', MedicalRecord::class));
        $this->assertFalse($secretary->can('view', $record));
        $this->assertFalse($secretary->can('update', $record));
        $this->assertFalse($secretary->can('delete', $record));
        $this->assertFalse($secretary->can('export', $record));
    }

    public function test_doctor_without_patient_relationship_has_no_access(): void
    {
        [$doctorUser] = $this->doctorUser();
        $record = Patient::factory()->create()->medicalRecord;

        $this->assertFalse($doctorUser->can('view', $record));
    }
}
