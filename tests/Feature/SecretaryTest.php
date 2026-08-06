<?php

namespace Tests\Feature;

use App\Enums\SecretaryStatus;
use App\Models\Secretary;
use App\Models\User;
use App\Services\SecretaryService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class SecretaryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_secretary_code_is_generated_automatically_and_unique(): void
    {
        $first = Secretary::factory()->create();
        $second = Secretary::factory()->create();

        $this->assertSame('SEC-000001', $first->secretary_code);
        $this->assertSame('SEC-000002', $second->secretary_code);
        $this->assertNotSame($first->secretary_code, $second->secretary_code);
    }

    public function test_secretary_code_does_not_collide_with_soft_deleted_records(): void
    {
        $first = Secretary::factory()->create();
        $first->delete();

        $second = Secretary::factory()->create();

        $this->assertSame('SEC-000001', $first->secretary_code);
        $this->assertSame('SEC-000002', $second->secretary_code);
    }

    public function test_secretary_can_be_soft_deleted_and_restored(): void
    {
        $secretary = Secretary::factory()->create();

        $secretary->delete();

        $this->assertSoftDeleted('secretaries', ['id' => $secretary->id]);
        $this->assertNull(Secretary::find($secretary->id));
        $this->assertNotNull(Secretary::withTrashed()->find($secretary->id));

        $secretary->restore();

        $this->assertNotNull(Secretary::find($secretary->id));
    }

    public function test_secretary_full_name_is_computed(): void
    {
        $secretary = Secretary::factory()->create([
            'first_name' => 'Amira',
            'last_name' => 'Gharbi',
        ]);

        $this->assertSame('Amira Gharbi', $secretary->full_name);
    }

    public function test_secretary_role_is_assigned_to_linked_user(): void
    {
        $secretary = Secretary::factory()->create();

        $this->assertNotNull($secretary->user);
        $this->assertTrue($secretary->user->hasRole('secretary'));
    }

    public function test_secretary_can_be_deactivated_and_reactivated(): void
    {
        $secretary = Secretary::factory()->create();
        $service = app(SecretaryService::class);

        $service->deactivate($secretary);
        $this->assertSame(SecretaryStatus::Inactive, $secretary->fresh()->status);

        $service->reactivate($secretary);
        $this->assertSame(SecretaryStatus::Active, $secretary->fresh()->status);
    }

    public function test_activity_is_logged_when_secretary_is_created(): void
    {
        $user = User::factory()->create();
        Auth::login($user);

        $secretary = Secretary::factory()->create();

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Secretary::class,
            'subject_id' => $secretary->id,
            'description' => 'Secrétaire créée',
        ]);

        $this->assertSame($user->id, Activity::latest()->first()->causer_id);
    }

    public function test_activity_is_logged_when_secretary_is_updated_deleted_and_deactivated(): void
    {
        $secretary = Secretary::factory()->create();

        $secretary->update(['phone' => '+21620123456']);
        app(SecretaryService::class)->deactivate($secretary);
        $secretary->delete();

        $descriptions = Activity::query()
            ->where('subject_type', Secretary::class)
            ->where('subject_id', $secretary->id)
            ->pluck('description')
            ->all();

        $this->assertContains('Secrétaire modifiée', $descriptions);
        $this->assertContains('Secrétaire désactivée', $descriptions);
        $this->assertContains('Secrétaire supprimée', $descriptions);
    }

    public function test_secretary_code_is_stable_throughout_updates(): void
    {
        $secretary = Secretary::factory()->create();
        $code = $secretary->secretary_code;

        $secretary->update(['status' => SecretaryStatus::Inactive]);

        $this->assertSame($code, $secretary->fresh()->secretary_code);
    }
}
