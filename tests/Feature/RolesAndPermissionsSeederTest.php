<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolesAndPermissionsSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_roles_and_permissions_are_created(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->assertDatabaseCount('roles', 6);
        $this->assertDatabaseCount('permissions', 57);

        $superAdmin = Role::findByName('super_admin');
        $this->assertCount(57, $superAdmin->permissions);

        $doctor = Role::findByName('doctor');
        $this->assertFalse($doctor->hasPermissionTo('consultations.manage'));
        $this->assertTrue($doctor->hasPermissionTo('consultations.view'));
        $this->assertTrue($doctor->hasPermissionTo('consultations.create'));
        $this->assertTrue($doctor->hasPermissionTo('consultations.update'));
        $this->assertTrue($doctor->hasPermissionTo('consultations.print'));
        $this->assertFalse($doctor->hasPermissionTo('consultations.delete'));
        $this->assertFalse($doctor->hasPermissionTo('billing.manage'));
        $this->assertTrue($doctor->hasPermissionTo('doctors.view'));
        $this->assertTrue($doctor->hasPermissionTo('medical_records.view'));
        $this->assertTrue($doctor->hasPermissionTo('allergies.manage'));
        $this->assertTrue($doctor->hasPermissionTo('medical_documents.download'));
        $this->assertFalse($doctor->hasPermissionTo('medical_records.delete'));
        $this->assertFalse($doctor->hasPermissionTo('medical_documents.delete'));
        $this->assertTrue($doctor->hasPermissionTo('prescriptions.view'));
        $this->assertTrue($doctor->hasPermissionTo('prescriptions.create'));
        $this->assertTrue($doctor->hasPermissionTo('prescriptions.update'));
        $this->assertTrue($doctor->hasPermissionTo('prescriptions.issue'));
        $this->assertTrue($doctor->hasPermissionTo('prescriptions.print'));
        $this->assertFalse($doctor->hasPermissionTo('prescriptions.delete'));
        $this->assertFalse($doctor->hasPermissionTo('prescriptions.cancel'));
        $this->assertFalse($doctor->hasPermissionTo('prescriptions.export'));

        $this->assertTrue(Permission::findByName('users.view')->exists);
    }

    public function test_admin_user_exists_with_super_admin_role(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $admin = User::where('email', 'admin@docta.com')->first();

        $this->assertNotNull($admin);
        $this->assertTrue($admin->hasRole('super_admin'));
        $this->assertTrue($admin->canAccessAdminPanel());
        $this->assertTrue($admin->isAdmin());
    }

    public function test_patient_role_does_not_have_panel_access(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $patient = User::factory()->create();
        $patient->assignRole('patient');

        $this->assertFalse($patient->canAccessAdminPanel());
    }
}
