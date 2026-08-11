<?php

namespace Tests\Feature;

use App\Enums\PrescriptionStatus;
use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\User;
use App\Services\PrescriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class PrescriptionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(PrescriptionService::class);
    }

    protected PrescriptionService $service;

    private function validData(array $overrides = []): array
    {
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();

        return $overrides + [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'consultation_id' => null,
            'prescription_date' => now()->toDateString(),
            'notes' => 'Prendre après les repas',
            'valid_until' => now()->addDays(7)->toDateString(),
        ];
    }

    private function validItems(int $count = 2): array
    {
        $items = [];

        for ($i = 1; $i <= $count; $i++) {
            $items[] = [
                'medicine_name' => 'Paracétamol '.$i,
                'active_ingredient' => 'Paracétamol',
                'dosage' => '500 mg',
                'form' => 'tablet',
                'route' => 'orale',
                'frequency' => '3 fois par jour',
                'duration' => '7',
                'duration_unit' => 'jour',
                'quantity' => '20',
                'instructions' => 'À prendre avec un verre d\'eau',
                'notes' => null,
            ];
        }

        return $items;
    }

    public function test_prescription_number_is_generated_sequentially(): void
    {
        $data = $this->validData();

        $first = $this->service->create($data, $this->validItems(1));
        $second = $this->service->create($data, $this->validItems(1));

        $this->assertSame('ORD-000001', $first->prescription_number);
        $this->assertSame('ORD-000002', $second->prescription_number);
        $this->assertNotSame($first->prescription_number, $second->prescription_number);
    }

    public function test_prescription_number_considers_soft_deleted_records(): void
    {
        $data = $this->validData();

        $first = $this->service->create($data, $this->validItems(1));
        $first->delete();

        $next = $this->service->create($data, $this->validItems(1));

        $this->assertSame('ORD-000002', $next->prescription_number);
    }

    public function test_create_stores_prescription_with_items_and_sequential_order(): void
    {
        $prescription = $this->service->create($this->validData(), $this->validItems(3));

        $this->assertDatabaseHas('prescriptions', [
            'id' => $prescription->id,
            'status' => PrescriptionStatus::Draft->value,
        ]);

        $this->assertSame(3, $prescription->items()->count());
        $this->assertSame([1, 2, 3], $prescription->items()->orderBy('sort_order')->pluck('sort_order')->all());
        $this->assertNotNull($prescription->verification_token);
        $this->assertTrue($prescription->isEditable());
    }

    public function test_create_rejects_empty_items(): void
    {
        try {
            $this->service->create($this->validData(), []);
            $this->fail('Une ordonnance sans médicament aurait dû être refusée.');
        } catch (HttpException $exception) {
            $this->assertSame(422, $exception->getStatusCode());
        }
    }

    public function test_issue_emits_a_draft_prescription(): void
    {
        $prescription = $this->service->create($this->validData(), $this->validItems(2));

        $this->service->issue($prescription);

        $prescription->refresh();

        $this->assertSame(PrescriptionStatus::Issued, $prescription->status);
        $this->assertFalse($prescription->isEditable());
    }

    public function test_issue_rejects_empty_prescription(): void
    {
        $prescription = Prescription::factory()->create([
            'patient_id' => Patient::factory(),
            'doctor_id' => Doctor::factory(),
            'status' => PrescriptionStatus::Draft,
        ]);

        try {
            $this->service->issue($prescription);
            $this->fail('Une ordonnance vide aurait dû être refusée.');
        } catch (HttpException $exception) {
            $this->assertSame(422, $exception->getStatusCode());
        }
    }

    public function test_update_rejects_non_draft_prescription(): void
    {
        $prescription = $this->service->create($this->validData(), $this->validItems(1));
        $this->service->issue($prescription);

        try {
            $this->service->update($prescription, $this->validData(), $this->validItems(1));
            $this->fail('Une ordonnance émise ne devrait plus être modifiable.');
        } catch (HttpException $exception) {
            $this->assertSame(409, $exception->getStatusCode());
        }
    }

    public function test_update_modifies_draft_prescription_and_replaces_items(): void
    {
        $prescription = $this->service->create($this->validData(), $this->validItems(1));

        $data = $this->validData(['notes' => 'Notes mises à jour']);

        $this->service->update($prescription, $data, $this->validItems(2));

        $prescription->refresh();

        $this->assertSame('Notes mises à jour', $prescription->notes);
        $this->assertSame(2, $prescription->items()->count());
    }

    public function test_cancel_closes_draft_and_issued_prescriptions(): void
    {
        $prescription = $this->service->create($this->validData(), $this->validItems(1));

        $this->service->cancel($prescription, 'Prescription obsolète');

        $prescription->refresh();

        $this->assertSame(PrescriptionStatus::Cancelled, $prescription->status);
    }

    public function test_cancel_rejects_already_cancelled_prescription(): void
    {
        $prescription = $this->service->create($this->validData(), $this->validItems(1));
        $this->service->cancel($prescription);

        try {
            $this->service->cancel($prescription);
            $this->fail('Une ordonnance déjà annulée ne devrait plus être annulable.');
        } catch (HttpException $exception) {
            $this->assertSame(409, $exception->getStatusCode());
        }
    }

    public function test_duplicate_creates_a_new_draft_copy(): void
    {
        $original = $this->service->create($this->validData(), $this->validItems(3));
        $this->service->issue($original);

        $copy = $this->service->duplicate($original);

        $this->assertNotSame($original->id, $copy->id);
        $this->assertNotSame($original->prescription_number, $copy->prescription_number);
        $this->assertSame(PrescriptionStatus::Draft, $copy->status);
        $this->assertSame($original->patient_id, $copy->patient_id);
        $this->assertSame($original->doctor_id, $copy->doctor_id);
        $this->assertSame(3, $copy->items()->count());
    }

    public function test_expire_overdue_marks_expired_issued_prescriptions(): void
    {
        $overdue = Prescription::factory()->create([
            'patient_id' => Patient::factory(),
            'doctor_id' => Doctor::factory(),
            'status' => PrescriptionStatus::Issued,
            'valid_until' => now()->subDay()->toDateString(),
        ]);
        Prescription::factory()->create([
            'patient_id' => Patient::factory(),
            'doctor_id' => Doctor::factory(),
            'status' => PrescriptionStatus::Issued,
            'valid_until' => now()->addDay()->toDateString(),
        ]);

        $count = $this->service->expireOverdue();

        $this->assertSame(1, $count);
        $this->assertSame(PrescriptionStatus::Expired, $overdue->fresh()->status);
    }

    public function test_doctor_cannot_create_prescription_for_another_doctor(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $actor = User::factory()->create();
        $actor->assignRole('doctor');
        $ownDoctor = Doctor::factory()->create(['user_id' => $actor->id]);
        $otherDoctor = Doctor::factory()->create();

        $this->actingAs($actor);

        try {
            $this->service->create(
                $this->validData(['doctor_id' => $otherDoctor->id]),
                $this->validItems(1),
            );
            $this->fail('Un médecin ne devrait pas pouvoir prescrire au nom d\'un autre médecin.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }

        $this->assertNotNull($ownDoctor);
    }

    public function test_doctor_can_create_prescription_for_himself(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $actor = User::factory()->create();
        $actor->assignRole('doctor');
        $doctor = Doctor::factory()->create(['user_id' => $actor->id]);

        $this->actingAs($actor);

        $prescription = $this->service->create(
            $this->validData(['doctor_id' => $doctor->id]),
            $this->validItems(1),
        );

        $this->assertSame($doctor->id, $prescription->doctor_id);
        $this->assertSame($actor->id, $prescription->created_by);
    }

    public function test_prescription_is_soft_deleted_only(): void
    {
        $prescription = $this->service->create($this->validData(), $this->validItems(1));

        $prescription->delete();

        $this->assertSoftDeleted('prescriptions', ['id' => $prescription->id]);
        $this->assertNotNull(Prescription::withTrashed()->find($prescription->id));
    }

    public function test_activity_is_logged_on_creation(): void
    {
        $prescription = $this->service->create($this->validData(), $this->validItems(1));

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Prescription::class,
            'subject_id' => $prescription->id,
        ]);
    }

    public function test_issue_logs_activity(): void
    {
        $prescription = $this->service->create($this->validData(), $this->validItems(1));

        $this->service->issue($prescription);

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Prescription::class,
            'subject_id' => $prescription->id,
            'description' => 'Ordonnance émise',
        ]);
    }

    public function test_for_consultation_keeps_patient_and_doctor_consistent(): void
    {
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();
        $consultation = Consultation::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'consultation_date' => now()->toDateString(),
        ]);

        $prescription = Prescription::factory()->forConsultation($consultation)->create();

        $this->assertSame($patient->id, $prescription->patient_id);
        $this->assertSame($doctor->id, $prescription->doctor_id);
        $this->assertSame($consultation->id, $prescription->consultation_id);
    }
}
