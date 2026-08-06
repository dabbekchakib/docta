<?php

namespace Tests\Feature;

use App\Enums\Permission;
use App\Models\Secretary;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecretaryPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_super_admin_can_manage_secretaries(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $this->assertTrue($admin->can('viewAny', Secretary::class));
        $this->assertTrue($admin->can('create', Secretary::class));
        $this->assertTrue($admin->can('export', Secretary::class));
    }

    public function test_user_with_secretaries_permissions_can_manage_secretaries(): void
    {
        $user = User::factory()->create();

        $user->givePermissionTo([
            Permission::SecretariesView->value,
            Permission::SecretariesCreate->value,
            Permission::SecretariesUpdate->value,
            Permission::SecretariesDelete->value,
        ]);

        $secretary = Secretary::factory()->create();

        $this->assertTrue($user->can('viewAny', Secretary::class));
        $this->assertTrue($user->can('view', $secretary));
        $this->assertTrue($user->can('create', Secretary::class));
        $this->assertTrue($user->can('update', $secretary));
        $this->assertTrue($user->can('delete', $secretary));
    }

    public function test_user_without_secretaries_permissions_cannot_manage_secretaries(): void
    {
        $user = User::factory()->create();
        $user->assignRole('patient');

        $secretary = Secretary::factory()->create();

        $this->assertFalse($user->can('viewAny', Secretary::class));
        $this->assertFalse($user->can('view', $secretary));
        $this->assertFalse($user->can('create', Secretary::class));
        $this->assertFalse($user->can('update', $secretary));
        $this->assertFalse($user->can('delete', $secretary));
        $this->assertFalse($user->can('export', Secretary::class));
    }
}
