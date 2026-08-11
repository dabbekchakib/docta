<?php

namespace Tests\Feature;

use App\Enums\AllergyStatus;
use App\Models\Allergy;
use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AllergyPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    protected function allergyFor(MedicalRecord $record): Allergy
    {
        return Allergy::factory()->create([
            'medical_record_id' => $record->id,
            'severity' => 'severe',
            'status' => AllergyStatus::Active->value,
        ]);
    }

    public function test_super_admin_can_manage_allergies(): void
    {
        $superAdmin = User::where('email', 'admin@docta.com')->firstOrFail();
        $record = Patient::factory()->create()->medicalRecord;
        $allergy = $this->allergyFor($record);

        $this->assertTrue($superAdmin->can('viewAny', Allergy::class));
        $this->assertTrue($superAdmin->can('create', Allergy::class));
        $this->assertTrue($superAdmin->can('view', $allergy));
        $this->assertTrue($superAdmin->can('update', $allergy));
        $this->assertTrue($superAdmin->can('delete', $allergy));
    }

    public function test_doctor_can_manage_allergies_of_his_patients(): void
    {
        $user = User::factory()->create();
        $user->assignRole('doctor');
        $doctor = Doctor::factory()->create(['user_id' => $user->id]);

        $patient = Patient::factory()->create();
        Consultation::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'consultation_date' => now()->toDateString(),
        ]);

        $own = $this->allergyFor($patient->medicalRecord);
        $other = $this->allergyFor(Patient::factory()->create()->medicalRecord);

        $this->assertTrue($user->can('viewAny', Allergy::class));
        $this->assertTrue($user->can('create', Allergy::class));
        $this->assertTrue($user->can('view', $own));
        $this->assertTrue($user->can('update', $own));
        $this->assertTrue($user->can('delete', $own));
        $this->assertFalse($user->can('view', $other));
        $this->assertFalse($user->can('update', $other));
        $this->assertFalse($user->can('delete', $other));
    }

    public function test_secretary_cannot_manage_allergies(): void
    {
        $secretary = User::factory()->create();
        $secretary->assignRole('secretary');
        $allergy = $this->allergyFor(Patient::factory()->create()->medicalRecord);

        $this->assertFalse($secretary->can('viewAny', Allergy::class));
        $this->assertFalse($secretary->can('create', Allergy::class));
        $this->assertFalse($secretary->can('view', $allergy));
        $this->assertFalse($secretary->can('update', $allergy));
        $this->assertFalse($secretary->can('delete', $allergy));
    }
}
