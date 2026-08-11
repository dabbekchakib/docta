<?php

namespace Tests\Feature;

use App\Enums\PrescriptionStatus;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrescriptionPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    protected function prescription(array $attributes = []): Prescription
    {
        return Prescription::factory()->create($attributes + [
            'patient_id' => Patient::factory(),
            'doctor_id' => Doctor::factory(),
            'status' => PrescriptionStatus::Draft,
        ]);
    }

    protected function doctorUser(): array
    {
        $user = User::factory()->create();
        $user->assignRole('doctor');
        $doctor = Doctor::factory()->create(['user_id' => $user->id]);

        return [$user, $doctor];
    }

    public function test_admin_can_manage_prescriptions(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $prescription = $this->prescription();

        $this->assertTrue($admin->can('viewAny', Prescription::class));
        $this->assertTrue($admin->can('view', $prescription));
        $this->assertTrue($admin->can('create', Prescription::class));
        $this->assertTrue($admin->can('update', $prescription));
        $this->assertTrue($admin->can('delete', $prescription));
        $this->assertTrue($admin->can('issue', $prescription));
        $this->assertTrue($admin->can('cancel', $prescription));
        $this->assertTrue($admin->can('print', $prescription));
        $this->assertTrue($admin->can('export', $prescription));
    }

    public function test_doctor_can_view_update_issue_and_print_only_his_own_prescriptions(): void
    {
        [$doctorUser, $doctor] = $this->doctorUser();
        $own = $this->prescription(['doctor_id' => $doctor->id]);
        $other = $this->prescription();

        $this->assertTrue($doctorUser->can('viewAny', Prescription::class));
        $this->assertTrue($doctorUser->can('view', $own));
        $this->assertTrue($doctorUser->can('update', $own));
        $this->assertTrue($doctorUser->can('issue', $own));
        $this->assertTrue($doctorUser->can('print', $own));
        $this->assertTrue($doctorUser->can('create', Prescription::class));
        $this->assertFalse($doctorUser->can('view', $other));
        $this->assertFalse($doctorUser->can('update', $other));
        $this->assertFalse($doctorUser->can('issue', $other));
        $this->assertFalse($doctorUser->can('print', $other));
    }

    public function test_doctor_cannot_delete_or_cancel_prescriptions(): void
    {
        [$doctorUser, $doctor] = $this->doctorUser();
        $prescription = $this->prescription(['doctor_id' => $doctor->id]);

        $this->assertFalse($doctorUser->can('delete', $prescription));
        $this->assertFalse($doctorUser->can('deleteAny', Prescription::class));
        $this->assertFalse($doctorUser->can('forceDelete', $prescription));
        $this->assertFalse($doctorUser->can('cancel', $prescription));
    }

    public function test_doctor_cannot_update_an_issued_prescription(): void
    {
        [$doctorUser, $doctor] = $this->doctorUser();
        $issued = $this->prescription([
            'doctor_id' => $doctor->id,
            'status' => PrescriptionStatus::Issued,
        ]);

        $this->assertTrue($doctorUser->can('view', $issued));
        $this->assertFalse($doctorUser->can('update', $issued));
        $this->assertFalse($doctorUser->can('issue', $issued));
    }

    public function test_secretary_can_view_and_print_but_not_manage(): void
    {
        $secretary = User::factory()->create();
        $secretary->assignRole('secretary');
        $prescription = $this->prescription();

        $this->assertTrue($secretary->can('viewAny', Prescription::class));
        $this->assertTrue($secretary->can('view', $prescription));
        $this->assertTrue($secretary->can('print', $prescription));
        $this->assertFalse($secretary->can('create', Prescription::class));
        $this->assertFalse($secretary->can('update', $prescription));
        $this->assertFalse($secretary->can('delete', $prescription));
        $this->assertFalse($secretary->can('issue', $prescription));
        $this->assertFalse($secretary->can('cancel', $prescription));
    }

    public function test_patient_role_cannot_access_prescriptions(): void
    {
        $patientUser = User::factory()->create();
        $patientUser->assignRole('patient');
        $prescription = $this->prescription();

        $this->assertFalse($patientUser->can('viewAny', Prescription::class));
        $this->assertFalse($patientUser->can('view', $prescription));
        $this->assertFalse($patientUser->can('print', $prescription));
    }

    public function test_super_admin_can_manage_prescriptions(): void
    {
        $superAdmin = User::where('email', 'admin@docta.com')->firstOrFail();
        $prescription = $this->prescription();

        $this->assertTrue($superAdmin->can('viewAny', Prescription::class));
        $this->assertTrue($superAdmin->can('view', $prescription));
        $this->assertTrue($superAdmin->can('delete', $prescription));
        $this->assertTrue($superAdmin->can('issue', $prescription));
        $this->assertTrue($superAdmin->can('restore', $prescription));
    }
}
