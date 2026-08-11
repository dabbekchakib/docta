<?php

namespace Tests\Feature;

use App\Enums\AllergySeverity;
use App\Enums\AllergyStatus;
use App\Models\Allergy;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Services\MedicalRecordService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MedicalRecordTest extends TestCase
{
    use RefreshDatabase;

    public function test_medical_record_is_automatically_created_for_patient(): void
    {
        $patient = Patient::factory()->create();

        $this->assertNotNull($patient->medicalRecord);
        $this->assertSame($patient->id, $patient->medicalRecord->patient_id);
        $this->assertMatchesRegularExpression('/^DMP-\d{6}$/', $patient->medicalRecord->medical_record_number);
    }

    public function test_medical_record_number_is_generated_sequentially(): void
    {
        $first = Patient::factory()->create()->medicalRecord;
        $second = Patient::factory()->create()->medicalRecord;

        $this->assertSame('DMP-000001', $first->medical_record_number);
        $this->assertSame('DMP-000002', $second->medical_record_number);
    }

    public function test_ensure_for_patient_is_idempotent(): void
    {
        $patient = Patient::factory()->create();

        $record = $patient->medicalRecord;

        $again = app(MedicalRecordService::class)->ensureForPatient($patient);

        $this->assertTrue($record->is($again));
        $this->assertSame(1, MedicalRecord::where('patient_id', $patient->id)->count());
    }

    public function test_medical_record_is_soft_deleted_only(): void
    {
        $record = Patient::factory()->create()->medicalRecord;

        $record->delete();

        $this->assertSoftDeleted('medical_records', ['id' => $record->id]);
        $this->assertNotNull(MedicalRecord::withTrashed()->find($record->id));
    }

    public function test_blood_group_is_copied_from_patient_on_auto_creation(): void
    {
        $patient = Patient::factory()->create(['blood_group' => 'A+']);

        $this->assertSame('A+', $patient->medicalRecord->blood_group->value);
        $this->assertSame('A+', $patient->medicalRecord->full_blood_group);
    }

    public function test_summary_returns_expected_structure(): void
    {
        $patient = Patient::factory()->create();
        $record = $patient->medicalRecord;

        $summary = app(MedicalRecordService::class)->summary($record);

        $this->assertSame($patient->full_name, $summary['patient']);
        $this->assertSame($patient->patient_number, $summary['patient_number']);
        $this->assertArrayHasKey('blood_group', $summary);
        $this->assertArrayHasKey('critical_allergies', $summary);
        $this->assertArrayHasKey('chronic_diseases', $summary);
        $this->assertArrayHasKey('medications', $summary);
    }

    public function test_critical_active_allergy_generates_alert(): void
    {
        $record = Patient::factory()->create()->medicalRecord;

        Allergy::factory()->critical()->create([
            'medical_record_id' => $record->id,
            'allergen' => 'Pénicilline',
        ]);

        $alerts = $record->alerts();

        $this->assertNotEmpty($alerts);
        $this->assertSame('Alerte allergie', $alerts->first()->title);
        $this->assertStringContainsString('Pénicilline', $alerts->first()->message);
    }

    public function test_non_critical_or_inactive_allergy_does_not_generate_alert(): void
    {
        $record = Patient::factory()->create()->medicalRecord;

        Allergy::factory()->create([
            'medical_record_id' => $record->id,
            'severity' => AllergySeverity::Mild,
            'status' => AllergyStatus::Active,
        ]);

        $this->assertTrue($record->alerts()->isEmpty());
    }

    public function test_timeline_is_sorted_descending(): void
    {
        $record = Patient::factory()->create()->medicalRecord;

        $record->medicalHistories()->create([
            'type' => 'maladie',
            'title' => 'Antécédent récent',
            'diagnosed_at' => now()->subDays(2)->format('Y-m-d'),
            'status' => 'active',
        ]);

        $record->medicalHistories()->create([
            'type' => 'maladie',
            'title' => 'Antécédent ancien',
            'diagnosed_at' => now()->subYears(3)->format('Y-m-d'),
            'status' => 'resolved',
        ]);

        $events = app(MedicalRecordService::class)->timeline($record);

        $this->assertCount(2, $events);
        $this->assertSame('Antécédent récent', $events->first()['title']);
        $this->assertSame('Antécédent ancien', $events->last()['title']);
    }
}
