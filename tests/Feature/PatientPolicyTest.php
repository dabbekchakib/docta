<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_admin_can_view_create_update_and_delete_patients(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $patient = Patient::factory()->create();

        $this->assertTrue($admin->can('viewAny', Patient::class));
        $this->assertTrue($admin->can('view', $patient));
        $this->assertTrue($admin->can('create', Patient::class));
        $this->assertTrue($admin->can('update', $patient));
        $this->assertTrue($admin->can('delete', $patient));
    }

    public function test_doctor_can_only_view_patients(): void
    {
        $doctor = User::factory()->create();
        $doctor->assignRole('doctor');
        $patient = Patient::factory()->create();

        $this->assertTrue($doctor->can('view', $patient));
        $this->assertFalse($doctor->can('create', Patient::class));
        $this->assertFalse($doctor->can('update', $patient));
        $this->assertFalse($doctor->can('delete', $patient));
    }

    public function test_secretary_can_create_and_update_but_not_delete_patients(): void
    {
        $secretary = User::factory()->create();
        $secretary->assignRole('secretary');
        $patient = Patient::factory()->create();

        $this->assertTrue($secretary->can('view', $patient));
        $this->assertTrue($secretary->can('create', Patient::class));
        $this->assertTrue($secretary->can('update', $patient));
        $this->assertFalse($secretary->can('delete', $patient));
    }

    public function test_accountant_can_only_view_patients(): void
    {
        $accountant = User::factory()->create();
        $accountant->assignRole('accountant');
        $patient = Patient::factory()->create();

        $this->assertTrue($accountant->can('view', $patient));
        $this->assertFalse($accountant->can('create', Patient::class));
        $this->assertFalse($accountant->can('delete', $patient));
    }

    public function test_super_admin_can_delete_patients(): void
    {
        $superAdmin = User::where('email', 'admin@docta.com')->firstOrFail();
        $patient = Patient::factory()->create();

        $this->assertTrue($superAdmin->can('delete', $patient));
    }
}
