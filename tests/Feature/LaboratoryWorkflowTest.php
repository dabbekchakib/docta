<?php

namespace Tests\Feature;

use App\Enums\LaboratoryRequestStatus;
use App\Enums\SampleType;
use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\Laboratory;
use App\Models\LaboratoryRequest;
use App\Models\LaboratoryResult;
use App\Models\LaboratoryTest;
use App\Models\Patient;
use App\Models\ReferenceRange;
use App\Models\Sample;
use App\Models\TestCategory;
use App\Notifications\LaboratoryResultsAvailableNotification;
use App\Services\LaboratoryReportService;
use App\Services\LaboratoryRequestService;
use App\Services\LaboratoryResultService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class LaboratoryWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private LaboratoryRequestService $requestService;

    private LaboratoryResultService $resultService;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        $this->requestService = app(LaboratoryRequestService::class);
        $this->resultService = app(LaboratoryResultService::class);
    }

    /**
     * @return array{patient: Patient, doctor: Doctor, consultation: Consultation, tests: \Illuminate\Support\Collection<int, LaboratoryTest>}
     */
    private function consultationContext(): array
    {
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();
        $consultation = Consultation::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
        ]);
        $tests = LaboratoryTest::factory()->count(2)->create();

        return [
            'patient' => $patient,
            'doctor' => $doctor,
            'consultation' => $consultation,
            'tests' => $tests,
        ];
    }

    /**
     * @return array{patient: Patient, doctor: Doctor, request: LaboratoryRequest}
     */
    private function submittedRequest(): array
    {
        $ctx = $this->consultationContext();

        $request = $this->requestService->create([
            'patient_id' => $ctx['patient']->id,
            'doctor_id' => $ctx['doctor']->id,
            'consultation_id' => $ctx['consultation']->id,
            'laboratory_id' => Laboratory::factory()->create()->id,
            'requested_at' => now()->toDateString(),
            'priority' => 'normal',
        ], $ctx['tests']
            ->map(fn (LaboratoryTest $test, int $index): array => [
                'laboratory_test_id' => $test->id,
                'sample_type' => $test->sample_type->value,
                'sort_order' => $index + 1,
            ])
            ->values()
            ->all());

        return [
            'patient' => $ctx['patient'],
            'doctor' => $ctx['doctor'],
            'request' => $this->requestService->submit($request),
        ];
    }

    public function test_create_generates_number_and_draft_status(): void
    {
        $ctx = $this->consultationContext();

        $request = $this->requestService->create([
            'patient_id' => $ctx['patient']->id,
            'doctor_id' => $ctx['doctor']->id,
            'consultation_id' => $ctx['consultation']->id,
            'requested_at' => now()->toDateString(),
        ], $ctx['tests']
            ->map(fn (LaboratoryTest $test, int $index): array => [
                'laboratory_test_id' => $test->id,
                'sample_type' => $test->sample_type->value,
                'sort_order' => $index + 1,
            ])
            ->values()
            ->all());

        $this->assertSame('LAB-000001', $request->request_number);
        $this->assertSame(LaboratoryRequestStatus::Draft, $request->status);
        $this->assertCount(2, $request->items);
    }

    public function test_submit_moves_request_to_requested(): void
    {
        $ctx = $this->submittedRequest();

        $this->assertSame(LaboratoryRequestStatus::Requested, $ctx['request']->status);
    }

    public function test_create_rejects_empty_request(): void
    {
        $ctx = $this->consultationContext();

        $this->expectExceptionMessage('Une demande d\'examen doit contenir au moins un examen.');

        $this->requestService->create([
            'patient_id' => $ctx['patient']->id,
            'doctor_id' => $ctx['doctor']->id,
            'requested_at' => now()->toDateString(),
        ], []);
    }

    public function test_collect_sample_sets_request_to_sample_collected(): void
    {
        $ctx = $this->submittedRequest();
        $request = $this->requestService->accept($ctx['request']);
        $item = $request->items->first();

        $sample = $this->requestService->collectSample($request, $item->id, 'À jeun');

        $this->assertSame('ECH-000001', $sample->sample_number);
        $this->assertSame('collected', $sample->status);
        $this->assertSame(LaboratoryRequestStatus::SampleCollected, $request->fresh()->status);
        $this->assertSame('sampled', $item->fresh()->status);
    }

    public function test_reject_sample(): void
    {
        $ctx = $this->submittedRequest();
        $request = $this->requestService->accept($ctx['request']);
        $item = $request->items->first();
        $sample = $this->requestService->collectSample($request, $item->id, null);

        $sample = $this->requestService->rejectSample($sample, 'Échantillon insuffisant');

        $this->assertSame('rejected', $sample->status);
        $this->assertSame('Échantillon insuffisant', $sample->rejection_reason);
        $this->assertTrue($sample->isRejected());
    }

    public function test_receive_and_process_samples(): void
    {
        $ctx = $this->submittedRequest();
        $request = $this->requestService->accept($ctx['request']);

        foreach ($request->items as $item) {
            $this->requestService->collectSample($request, $item->id, null);
        }

        $received = $this->requestService->receiveSamples($request);
        $processed = $this->requestService->processSamples($request);

        $this->assertSame(2, $received);
        $this->assertSame(2, $processed);
        $this->assertSame(LaboratoryRequestStatus::InAnalysis, $request->fresh()->status);
    }

    public function test_sync_results_marks_request_results_entered(): void
    {
        $ctx = $this->submittedRequest();
        $request = $this->requestService->accept($ctx['request']);

        $rows = $request->items
            ->map(fn ($item, int $index): array => [
                'laboratory_request_item_id' => $item->id,
                'parameter_name' => $item->test?->name,
                'value' => (string) ($index + 1),
                'numeric_value' => (string) ($index + 1),
                'unit' => $item->test?->unit,
                'abnormality' => 'normal',
            ])
            ->values()
            ->all();

        $this->resultService->syncResults($request, $rows);

        $this->assertSame(LaboratoryRequestStatus::ResultsEntered, $request->fresh()->status);
        $this->assertTrue($request->fresh()->hasEnteredResults());
        $this->assertTrue($request->fresh()->allItemsHaveResults());
    }

    public function test_validate_marks_results_validated_and_notifies_doctor(): void
    {
        $ctx = $this->submittedRequest();
        $request = $this->requestService->accept($ctx['request']);

        $rows = $request->items
            ->map(fn ($item, int $index): array => [
                'laboratory_request_item_id' => $item->id,
                'parameter_name' => $item->test?->name,
                'value' => (string) ($index + 1),
                'numeric_value' => (string) ($index + 1),
                'abnormality' => 'normal',
            ])
            ->values()
            ->all();

        $this->resultService->syncResults($request, $rows);

        $request = $this->resultService->validate($request->fresh());

        $this->assertSame(LaboratoryRequestStatus::Validated, $request->status);
        $this->assertTrue($request->results()->get()->every(fn (LaboratoryResult $r): bool => $r->isValidated()));

        Notification::assertSentTo($ctx['doctor']->user, LaboratoryResultsAvailableNotification::class);
    }

    public function test_validate_rejects_when_results_missing(): void
    {
        $ctx = $this->submittedRequest();
        $request = $this->requestService->accept($ctx['request']);

        $this->expectExceptionMessage('Aucun résultat à valider.');

        $this->resultService->validate($request);
    }

    public function test_record_correction_is_traced(): void
    {
        $ctx = $this->submittedRequest();
        $request = $this->requestService->accept($ctx['request']);

        $rows = $request->items
            ->map(fn ($item): array => [
                'laboratory_request_item_id' => $item->id,
                'parameter_name' => $item->test?->name,
                'value' => '10',
                'numeric_value' => 10,
                'abnormality' => 'normal',
            ])
            ->values()
            ->all();

        $this->resultService->syncResults($request, $rows);
        $request = $this->resultService->validate($request->fresh());

        $result = $request->results()->first();
        $corrected = $this->resultService->recordCorrection($result, '12', 12.0, 'Erreur de saisie');

        $this->assertSame('12', $corrected->value);
        $this->assertCount(1, $corrected->versions);
        $this->assertSame('10', $corrected->versions->first()->previous_value);
    }

    public function test_cancel_marks_request_cancelled(): void
    {
        $ctx = $this->submittedRequest();

        $request = $this->requestService->cancel($ctx['request'], 'Demande annulée par le patient');

        $this->assertSame(LaboratoryRequestStatus::Cancelled, $request->status);
    }

    public function test_reference_range_resolution_for_adult_male(): void
    {
        $test = LaboratoryTest::factory()->create();
        $male = ReferenceRange::factory()->create([
            'laboratory_test_id' => $test->id,
            'gender' => 'male',
            'age_min' => 18,
            'age_max' => 65,
            'min_value' => 1,
            'max_value' => 10,
        ]);
        ReferenceRange::factory()->create([
            'laboratory_test_id' => $test->id,
            'gender' => 'all',
            'min_value' => 0,
            'max_value' => 5,
        ]);

        $patient = Patient::factory()->create([
            'gender' => \App\Enums\PatientGender::Male,
            'birth_date' => now()->subYears(40)->toDateString(),
        ]);

        $resolved = $this->resultService->resolveReferenceRange($test, $patient);

        $this->assertSame($male->id, $resolved?->id);
    }

    public function test_report_generation_is_idempotent(): void
    {
        $ctx = $this->submittedRequest();
        $request = $this->requestService->accept($ctx['request']);

        $rows = $request->items
            ->map(fn ($item): array => [
                'laboratory_request_item_id' => $item->id,
                'parameter_name' => $item->test?->name,
                'value' => '5',
                'numeric_value' => 5,
                'abnormality' => 'normal',
            ])
            ->values()
            ->all();

        $this->resultService->syncResults($request, $rows);
        $request = $this->resultService->validate($request->fresh());

        $reportService = app(LaboratoryReportService::class);
        $first = $reportService->generate($request);
        $second = $reportService->generate($request->fresh());

        $this->assertSame($first->id, $second->id);
        $this->assertNotNull($first->report_number);
    }

    public function test_doctor_cannot_create_request_for_another_doctor(): void
    {
        $ctx = $this->consultationContext();
        $actorUser = \App\Models\User::factory()->create();
        $actorUser->assignRole('doctor');

        $otherDoctor = Doctor::factory()->create();
        $actorDoctor = Doctor::factory()->create(['user_id' => $actorUser->id]);

        $this->actingAs($actorUser);

        $this->expectExceptionMessage('Un médecin ne peut pas créer une demande d\'examen au nom d\'un autre médecin.');

        $this->requestService->create([
            'patient_id' => $ctx['patient']->id,
            'doctor_id' => $otherDoctor->id,
            'requested_at' => now()->toDateString(),
        ], [
            ['laboratory_test_id' => $ctx['tests']->first()->id, 'sample_type' => SampleType::Blood->value],
        ]);
    }

    public function test_sample_number_sequence(): void
    {
        $ctx = $this->submittedRequest();
        $request = $this->requestService->accept($ctx['request']);
        $item = $request->items->first();

        $this->requestService->collectSample($request, $item->id, null);
        $this->assertSame('ECH-000002', $this->requestService->generateSampleNumber());
    }
}
