<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Enums\DoctorStatus;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use App\Services\AppointmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AppointmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_appointment_number_is_generated_automatically_and_unique(): void
    {
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();

        $first = Appointment::factory()->create(['patient_id' => $patient->id, 'doctor_id' => $doctor->id]);
        $second = Appointment::factory()->create(['patient_id' => $patient->id, 'doctor_id' => $doctor->id]);

        $this->assertSame('RDV-000001', $first->appointment_number);
        $this->assertSame('RDV-000002', $second->appointment_number);
        $this->assertNotSame($first->appointment_number, $second->appointment_number);
    }

    public function test_appointment_number_does_not_collide_with_soft_deleted_records(): void
    {
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();

        $first = Appointment::factory()->create(['patient_id' => $patient->id, 'doctor_id' => $doctor->id]);
        $first->delete();

        $second = Appointment::factory()->create(['patient_id' => $patient->id, 'doctor_id' => $doctor->id]);

        $this->assertSame('RDV-000001', $first->appointment_number);
        $this->assertSame('RDV-000002', $second->appointment_number);
    }

    public function test_appointment_can_be_soft_deleted_and_restored(): void
    {
        $appointment = Appointment::factory()->create();

        $appointment->delete();

        $this->assertSoftDeleted('appointments', ['id' => $appointment->id]);
        $this->assertNull(Appointment::find($appointment->id));
        $this->assertNotNull(Appointment::withTrashed()->find($appointment->id));

        $appointment->restore();

        $this->assertNotNull(Appointment::find($appointment->id));
    }

    public function test_end_time_is_computed_from_start_time_and_duration(): void
    {
        $appointment = Appointment::factory()->create([
            'start_time' => '09:00',
            'duration' => 30,
        ]);

        $this->assertSame('09:30', $appointment->end_time);
        $this->assertSame(30, $appointment->duration);
    }

    public function test_status_defaults_to_pending(): void
    {
        $appointment = Appointment::factory()->create(['status' => null]);

        $this->assertTrue($appointment->status === AppointmentStatus::Pending);
    }

    public function test_activity_is_logged_when_appointment_is_created(): void
    {
        $user = User::factory()->create();
        Auth::login($user);

        $appointment = Appointment::factory()->create();

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Appointment::class,
            'subject_id' => $appointment->id,
            'description' => 'Rendez-vous créé',
        ]);
    }

    public function test_activity_is_logged_when_appointment_is_updated_and_deleted(): void
    {
        $appointment = Appointment::factory()->create();

        $appointment->update(['notes' => 'Test']);
        $appointment->delete();

        $descriptions = \Spatie\Activitylog\Models\Activity::query()
            ->where('subject_type', Appointment::class)
            ->where('subject_id', $appointment->id)
            ->pluck('description')
            ->all();

        $this->assertContains('Rendez-vous modifié', $descriptions);
        $this->assertContains('Rendez-vous supprimé', $descriptions);
    }

    public function test_appointment_number_is_stable_throughout_updates(): void
    {
        $appointment = Appointment::factory()->create();
        $number = $appointment->appointment_number;

        $appointment->update(['notes' => 'Test']);

        $this->assertSame($number, $appointment->fresh()->appointment_number);
    }

    public function test_conflicting_appointments_are_detected(): void
    {
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create(['status' => DoctorStatus::Active]);
        $date = today()->addDays(5)->toDateString();

        Appointment::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => $date,
            'start_time' => '09:00',
            'duration' => 30,
            'status' => AppointmentStatus::Confirmed,
        ]);

        $service = app(AppointmentService::class);

        $this->assertFalse($service->isDoctorAvailable($doctor, $date, '09:15', '09:45'));
        $this->assertFalse($service->isDoctorAvailable($doctor, $date, '08:30', '09:30'));
        $this->assertTrue($service->isDoctorAvailable($doctor, $date, '10:00', '10:30'));
        $this->assertFalse($service->isDoctorAvailable($doctor, $date, '09:00', '09:00'));
    }

    public function test_cancelled_appointments_do_not_block_the_slot(): void
    {
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create(['status' => DoctorStatus::Active]);
        $date = today()->addDays(5)->toDateString();

        Appointment::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => $date,
            'start_time' => '09:00',
            'duration' => 30,
            'status' => AppointmentStatus::Cancelled,
        ]);

        $this->assertTrue(app(AppointmentService::class)->isDoctorAvailable($doctor, $date, '09:15', '09:45'));
    }

    public function test_inactive_doctor_is_not_available(): void
    {
        $doctor = Doctor::factory()->create(['status' => DoctorStatus::Inactive]);

        $this->assertFalse(
            app(AppointmentService::class)->isDoctorAvailable($doctor, today()->addDays(5)->toDateString(), '09:00', '09:30')
        );
    }

    public function test_service_throws_validation_exception_on_conflict(): void
    {
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create(['status' => DoctorStatus::Active]);
        $date = today()->addDays(5)->toDateString();

        Appointment::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => $date,
            'start_time' => '09:00',
            'duration' => 30,
            'status' => AppointmentStatus::Confirmed,
        ]);

        $this->expectException(ValidationException::class);

        app(AppointmentService::class)->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => $date,
            'start_time' => '09:15',
            'duration' => 30,
        ]);
    }

    public function test_confirm_sets_status_and_confirmed_at(): void
    {
        $appointment = Appointment::factory()->create();

        app(AppointmentService::class)->confirm($appointment);

        $this->assertTrue($appointment->fresh()->status === AppointmentStatus::Confirmed);
        $this->assertNotNull($appointment->fresh()->confirmed_at);
    }

    public function test_cancel_sets_status_and_cancelled_at(): void
    {
        $appointment = Appointment::factory()->create();

        app(AppointmentService::class)->cancel($appointment);

        $this->assertTrue($appointment->fresh()->status === AppointmentStatus::Cancelled);
        $this->assertNotNull($appointment->fresh()->cancelled_at);
    }
}
