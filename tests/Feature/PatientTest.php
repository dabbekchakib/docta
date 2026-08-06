<?php

namespace Tests\Feature;

use App\Enums\PatientStatus;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class PatientTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_number_is_generated_automatically_and_unique(): void
    {
        $first = Patient::factory()->create();
        $second = Patient::factory()->create();

        $this->assertSame('PAT-000001', $first->patient_number);
        $this->assertSame('PAT-000002', $second->patient_number);
        $this->assertNotSame($first->patient_number, $second->patient_number);
    }

    public function test_patient_number_does_not_collide_with_soft_deleted_records(): void
    {
        $first = Patient::factory()->create();
        $first->delete();

        $second = Patient::factory()->create();

        $this->assertSame('PAT-000001', $first->patient_number);
        $this->assertSame('PAT-000002', $second->patient_number);
    }

    public function test_patient_can_be_soft_deleted_and_restored(): void
    {
        $patient = Patient::factory()->create();

        $patient->delete();

        $this->assertSoftDeleted('patients', ['id' => $patient->id]);
        $this->assertNull(Patient::find($patient->id));
        $this->assertNotNull(Patient::withTrashed()->find($patient->id));

        $patient->restore();

        $this->assertNotNull(Patient::find($patient->id));
    }

    public function test_patient_age_is_computed_from_birth_date(): void
    {
        $patient = Patient::factory()->create([
            'birth_date' => now()->subYears(30)->subMonths(2)->toDateString(),
        ]);

        $this->assertSame(30, $patient->age);
    }

    public function test_patient_age_is_null_without_birth_date(): void
    {
        $patient = Patient::factory()->create(['birth_date' => null]);

        $this->assertNull($patient->age);
    }

    public function test_patient_full_name_is_computed(): void
    {
        $patient = Patient::factory()->create([
            'first_name' => 'Sofien',
            'last_name' => 'Trabelsi',
        ]);

        $this->assertSame('Sofien Trabelsi', $patient->full_name);
    }

    public function test_activity_is_logged_when_patient_is_created(): void
    {
        $user = User::factory()->create();
        Auth::login($user);

        $patient = Patient::factory()->create();

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Patient::class,
            'subject_id' => $patient->id,
            'description' => 'Patient créé',
        ]);

        $this->assertSame($user->id, Activity::latest()->first()->causer_id);
    }

    public function test_activity_is_logged_when_patient_is_updated_and_deleted(): void
    {
        $patient = Patient::factory()->create();

        $patient->update(['phone' => '+21620123456']);
        $patient->delete();

        $descriptions = Activity::query()
            ->where('subject_type', Patient::class)
            ->where('subject_id', $patient->id)
            ->pluck('description')
            ->all();

        $this->assertContains('Patient modifié', $descriptions);
        $this->assertContains('Patient supprimé', $descriptions);
    }

    public function test_patient_number_is_stable_throughout_updates(): void
    {
        $patient = Patient::factory()->create();
        $number = $patient->patient_number;

        $patient->update(['status' => PatientStatus::Archived]);

        $this->assertSame($number, $patient->fresh()->patient_number);
    }
}
