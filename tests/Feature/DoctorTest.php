<?php

namespace Tests\Feature;

use App\Enums\DoctorStatus;
use App\Models\Doctor;
use App\Models\User;
use App\Services\DoctorService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class DoctorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_doctor_code_is_generated_automatically_and_unique(): void
    {
        $first = Doctor::factory()->create();
        $second = Doctor::factory()->create();

        $this->assertSame('DOC-000001', $first->doctor_code);
        $this->assertSame('DOC-000002', $second->doctor_code);
        $this->assertNotSame($first->doctor_code, $second->doctor_code);
    }

    public function test_doctor_code_does_not_collide_with_soft_deleted_records(): void
    {
        $first = Doctor::factory()->create();
        $first->delete();

        $second = Doctor::factory()->create();

        $this->assertSame('DOC-000001', $first->doctor_code);
        $this->assertSame('DOC-000002', $second->doctor_code);
    }

    public function test_doctor_can_be_soft_deleted_and_restored(): void
    {
        $doctor = Doctor::factory()->create();

        $doctor->delete();

        $this->assertSoftDeleted('doctors', ['id' => $doctor->id]);
        $this->assertNull(Doctor::find($doctor->id));
        $this->assertNotNull(Doctor::withTrashed()->find($doctor->id));

        $doctor->restore();

        $this->assertNotNull(Doctor::find($doctor->id));
    }

    public function test_doctor_full_name_is_computed(): void
    {
        $doctor = Doctor::factory()->create([
            'first_name' => 'Sofien',
            'last_name' => 'Trabelsi',
        ]);

        $this->assertSame('Sofien Trabelsi', $doctor->full_name);
    }

    public function test_doctor_role_is_assigned_to_linked_user(): void
    {
        $doctor = Doctor::factory()->create();

        $this->assertNotNull($doctor->user);
        $this->assertTrue($doctor->user->hasRole('doctor'));
    }

    public function test_doctor_can_be_deactivated_and_reactivated(): void
    {
        $doctor = Doctor::factory()->create();
        $service = app(DoctorService::class);

        $service->deactivate($doctor);
        $this->assertSame(DoctorStatus::Inactive, $doctor->fresh()->status);

        $service->reactivate($doctor);
        $this->assertSame(DoctorStatus::Active, $doctor->fresh()->status);
    }

    public function test_activity_is_logged_when_doctor_is_created(): void
    {
        $user = User::factory()->create();
        Auth::login($user);

        $doctor = Doctor::factory()->create();

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Doctor::class,
            'subject_id' => $doctor->id,
            'description' => 'Médecin créé',
        ]);

        $this->assertSame($user->id, Activity::latest()->first()->causer_id);
    }

    public function test_activity_is_logged_when_doctor_is_updated_deleted_and_deactivated(): void
    {
        $doctor = Doctor::factory()->create();

        $doctor->update(['phone' => '+21620123456']);
        app(DoctorService::class)->deactivate($doctor);
        $doctor->delete();

        $descriptions = Activity::query()
            ->where('subject_type', Doctor::class)
            ->where('subject_id', $doctor->id)
            ->pluck('description')
            ->all();

        $this->assertContains('Médecin modifié', $descriptions);
        $this->assertContains('Médecin désactivé', $descriptions);
        $this->assertContains('Médecin supprimé', $descriptions);
    }

    public function test_doctor_code_is_stable_throughout_updates(): void
    {
        $doctor = Doctor::factory()->create();
        $code = $doctor->doctor_code;

        $doctor->update(['status' => DoctorStatus::Inactive]);

        $this->assertSame($code, $doctor->fresh()->doctor_code);
    }
}
