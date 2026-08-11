<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Enums\AppointmentType;
use App\Enums\ConsultationStatus;
use App\Enums\ConsultationType;
use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use App\Models\VitalSign;
use App\Services\ConsultationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ConsultationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
    }

    public function test_consultation_number_is_generated_sequentially(): void
    {
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();

        $first = Consultation::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'consultation_date' => now()->toDateString(),
        ]);
        $second = Consultation::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'consultation_date' => now()->toDateString(),
        ]);

        $this->assertSame('CONS-000001', $first->consultation_number);
        $this->assertSame('CONS-000002', $second->consultation_number);
        $this->assertTrue($first->consultation_number !== $second->consultation_number);
    }

    public function test_consultation_number_considers_soft_deleted_records(): void
    {
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();

        $first = Consultation::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'consultation_date' => now()->toDateString(),
        ]);

        $first->delete();

        $next = Consultation::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'consultation_date' => now()->toDateString(),
        ]);

        $this->assertSame('CONS-000002', $next->consultation_number);
    }

    public function test_defaults_are_applied_on_create(): void
    {
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();
        $user = User::factory()->create();

        $consultation = Consultation::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'consultation_date' => now()->toDateString(),
            'status' => null,
            'type' => null,
            'created_by' => $user->id,
        ]);

        $this->assertSame(ConsultationStatus::Scheduled, $consultation->status);
        $this->assertSame(ConsultationType::FirstVisit, $consultation->type);
        $this->assertSame($user->id, $consultation->created_by);
    }

    public function test_start_from_appointment_creates_consultation_and_changes_status(): void
    {
        $appointment = Appointment::factory()->create([
            'status' => AppointmentStatus::Confirmed,
            'appointment_date' => today()->toDateString(),
            'start_time' => '09:00',
            'reason' => 'Douleurs abdominales',
            'type' => AppointmentType::Urgent,
        ]);

        $consultation = app(ConsultationService::class)->startFromAppointment($appointment);

        $this->assertDatabaseHas('consultations', [
            'id' => $consultation->id,
            'patient_id' => $appointment->patient_id,
            'doctor_id' => $appointment->doctor_id,
            'appointment_id' => $appointment->id,
            'consultation_date' => $appointment->appointment_date->format('Y-m-d 00:00:00'),
            'reason' => 'Douleurs abdominales',
            'status' => ConsultationStatus::InProgress->value,
        ]);

        $this->assertSame(ConsultationType::Emergency, $consultation->type);

        $appointment->refresh();
        $this->assertSame(AppointmentStatus::InProgress, $appointment->status);
    }

    public function test_complete_marks_consultation_and_appointment_as_completed(): void
    {
        $appointment = Appointment::factory()->create([
            'status' => AppointmentStatus::InProgress,
            'appointment_date' => today()->toDateString(),
        ]);

        $consultation = app(ConsultationService::class)->startFromAppointment($appointment);

        $completed = app(ConsultationService::class)->complete($consultation);

        $this->assertSame(ConsultationStatus::Completed, $completed->status);
        $this->assertNotNull($completed->completed_at);

        $appointment->refresh();
        $this->assertSame(AppointmentStatus::Completed, $appointment->status);
    }

    public function test_cancel_marks_consultation_as_cancelled(): void
    {
        $consultation = Consultation::factory()->create([
            'consultation_date' => now()->toDateString(),
        ]);

        app(ConsultationService::class)->cancel($consultation, 'Doublon');

        $this->assertSame(ConsultationStatus::Cancelled, $consultation->status);
        $this->assertNotNull($consultation->cancelled_at);
    }

    public function test_bmi_is_computed_automatically(): void
    {
        $consultation = Consultation::factory()->create([
            'consultation_date' => now()->toDateString(),
        ]);

        $vital = VitalSign::factory()->create([
            'consultation_id' => $consultation->id,
            'weight' => 70,
            'height' => 175,
        ]);

        $this->assertEqualsWithDelta(22.9, (float) $vital->bmi, 0.1);
    }

    public function test_consultation_is_soft_deleted_only(): void
    {
        $consultation = Consultation::factory()->create([
            'consultation_date' => now()->toDateString(),
        ]);

        $consultation->delete();

        $this->assertSoftDeleted('consultations', ['id' => $consultation->id]);
        $this->assertNotNull(Consultation::withTrashed()->find($consultation->id));
    }

    public function test_activity_is_logged_on_creation(): void
    {
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();

        $consultation = Consultation::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'consultation_date' => now()->toDateString(),
        ]);

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Consultation::class,
            'subject_id' => $consultation->id,
            'description' => 'Consultation créée',
        ]);
    }

    public function test_appointment_type_is_mapped_to_consultation_type(): void
    {
        $service = app(ConsultationService::class);

        $this->assertSame(ConsultationType::Control, $service->mapAppointmentType(AppointmentType::Control));
        $this->assertSame(ConsultationType::FollowUp, $service->mapAppointmentType(AppointmentType::FollowUp));
        $this->assertSame(ConsultationType::Emergency, $service->mapAppointmentType(AppointmentType::Urgent));
        $this->assertSame(ConsultationType::Teleconsultation, $service->mapAppointmentType(AppointmentType::Teleconsultation));
        $this->assertSame(ConsultationType::FirstVisit, $service->mapAppointmentType(AppointmentType::Consultation));
        $this->assertSame(ConsultationType::FirstVisit, $service->mapAppointmentType(null));
    }
}
