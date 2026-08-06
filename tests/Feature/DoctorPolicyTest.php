<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DoctorPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_admin_can_view_create_update_delete_and_export_doctors(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $doctor = Doctor::factory()->create();

        $this->assertTrue($admin->can('viewAny', Doctor::class));
        $this->assertTrue($admin->can('view', $doctor));
        $this->assertTrue($admin->can('create', Doctor::class));
        $this->assertTrue($admin->can('update', $doctor));
        $this->assertTrue($admin->can('delete', $doctor));
        $this->assertTrue($admin->can('export', Doctor::class));
    }

    public function test_doctor_role_can_only_view_doctors(): void
    {
        $doctorUser = User::factory()->create();
        $doctorUser->assignRole('doctor');
        $doctor = Doctor::factory()->create();

        $this->assertTrue($doctorUser->can('view', $doctor));
        $this->assertFalse($doctorUser->can('create', Doctor::class));
        $this->assertFalse($doctorUser->can('update', $doctor));
        $this->assertFalse($doctorUser->can('delete', $doctor));
        $this->assertFalse($doctorUser->can('export', Doctor::class));
    }

    public function test_secretary_can_view_but_not_manage_doctors(): void
    {
        $secretary = User::factory()->create();
        $secretary->assignRole('secretary');
        $doctor = Doctor::factory()->create();

        $this->assertTrue($secretary->can('view', $doctor));
        $this->assertFalse($secretary->can('create', Doctor::class));
        $this->assertFalse($secretary->can('update', $doctor));
        $this->assertFalse($secretary->can('delete', $doctor));
    }

    public function test_accountant_cannot_view_doctors(): void
    {
        $accountant = User::factory()->create();
        $accountant->assignRole('accountant');
        $doctor = Doctor::factory()->create();

        $this->assertFalse($accountant->can('view', $doctor));
    }

    public function test_super_admin_can_manage_doctors(): void
    {
        $superAdmin = User::where('email', 'admin@docta.com')->firstOrFail();
        $doctor = Doctor::factory()->create();

        $this->assertTrue($superAdmin->can('delete', $doctor));
        $this->assertTrue($superAdmin->can('export', Doctor::class));
    }
}
