<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_guest_is_redirected_to_admin_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_super_admin_can_access_admin_dashboard(): void
    {
        $admin = User::where('email', 'admin@docta.com')->firstOrFail();

        $this->actingAs($admin)->get('/admin')->assertOk();
    }

    public function test_patient_cannot_access_admin_panel(): void
    {
        $patient = User::factory()->create();
        $patient->assignRole('patient');

        $this->actingAs($patient)->get('/admin')->assertForbidden();
    }

    public function test_super_admin_can_view_users_resource(): void
    {
        $admin = User::where('email', 'admin@docta.com')->firstOrFail();

        $this->actingAs($admin)->get('/admin/users')->assertOk();
    }

    public function test_doctor_without_users_permission_cannot_view_users_resource(): void
    {
        $doctor = User::factory()->create();
        $doctor->assignRole('doctor');

        $this->actingAs($doctor)->get('/admin/users')->assertForbidden();
    }
}
