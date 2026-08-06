<?php

namespace Tests\Feature;

use App\Enums\DoctorStatus;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_admin_can_manage_appointments(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $appointment = Appointment::factory()->create();

        $this->assertTrue($admin->can('viewAny', Appointment::class));
        $this->assertTrue($admin->can('create', Appointment::class));
        $this->assertTrue($admin->can('view', $appointment));
        $this->assertTrue($admin->can('update', $appointment));
        $this->assertTrue($admin->can('delete', $appointment));
        $this->assertTrue($admin->can('deleteAny', Appointment::class));
        $this->assertTrue($admin->can('confirm', $appointment));
        $this->assertTrue($admin->can('cancel', $appointment));
        $this->assertTrue($admin->can('calendar', Appointment::class));
    }

    public function test_doctor_can_only_manage_his_own_appointments(): void
    {
        $doctorUser = User::factory()->create();
        $doctorUser->assignRole('doctor');
        $doctor = Doctor::factory()->create([
            'user_id' => $doctorUser->id,
            'status' => DoctorStatus::Active,
        ]);
        $own = Appointment::factory()->create(['doctor_id' => $doctor->id]);
        $other = Appointment::factory()->create();

        $this->assertTrue($doctorUser->can('viewAny', Appointment::class));
        $this->assertTrue($doctorUser->can('create', Appointment::class));
        $this->assertTrue($doctorUser->can('view', $own));
        $this->assertTrue($doctorUser->can('update', $own));
        $this->assertTrue($doctorUser->can('confirm', $own));
        $this->assertTrue($doctorUser->can('cancel', $own));
        $this->assertTrue($doctorUser->can('calendar', Appointment::class));

        $this->assertFalse($doctorUser->can('view', $other));
        $this->assertFalse($doctorUser->can('update', $other));
        $this->assertFalse($doctorUser->can('delete', $own));
    }

    public function test_secretary_can_manage_appointments(): void
    {
        $secretary = User::factory()->create();
        $secretary->assignRole('secretary');
        $appointment = Appointment::factory()->create();

        $this->assertTrue($secretary->can('viewAny', Appointment::class));
        $this->assertTrue($secretary->can('create', Appointment::class));
        $this->assertTrue($secretary->can('view', $appointment));
        $this->assertTrue($secretary->can('update', $appointment));
        $this->assertTrue($secretary->can('delete', $appointment));
        $this->assertTrue($secretary->can('confirm', $appointment));
        $this->assertTrue($secretary->can('cancel', $appointment));
        $this->assertTrue($secretary->can('calendar', Appointment::class));
    }

    public function test_accountant_cannot_view_appointments(): void
    {
        $accountant = User::factory()->create();
        $accountant->assignRole('accountant');
        $appointment = Appointment::factory()->create();

        $this->assertFalse($accountant->can('viewAny', Appointment::class));
        $this->assertFalse($accountant->can('view', $appointment));
    }
}
