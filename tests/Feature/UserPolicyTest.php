<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_admin_role_can_create_users(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->assertTrue($admin->can('create', User::class));
        $this->assertTrue($admin->can('viewAny', User::class));
    }

    public function test_doctor_role_cannot_manage_users(): void
    {
        $doctor = User::factory()->create();
        $doctor->assignRole('doctor');
        $target = User::factory()->create();

        $this->assertFalse($doctor->can('create', User::class));
        $this->assertFalse($doctor->can('viewAny', User::class));
        $this->assertFalse($doctor->can('delete', $target));
    }

    public function test_super_admin_can_delete_other_users(): void
    {
        $superAdmin = User::where('email', 'admin@docta.com')->firstOrFail();
        $other = User::factory()->create();

        $this->assertTrue($superAdmin->can('delete', $other));
    }

    public function test_user_cannot_delete_their_own_account(): void
    {
        $superAdmin = User::where('email', 'admin@docta.com')->firstOrFail();

        $this->assertFalse($superAdmin->can('delete', $superAdmin));
    }
}
