<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Enums\AppointmentType;
use App\Filament\Pages\AppointmentsCalendar;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AppointmentsCalendarTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function createAppointment(Patient $patient, Doctor $doctor, string $startTime = '09:00'): Appointment
    {
        return Appointment::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => now()->format('Y-m-d'),
            'start_time' => $startTime,
            'end_time' => \Illuminate\Support\Carbon::parse($startTime)->addMinutes(30)->format('H:i'),
            'status' => AppointmentStatus::Confirmed,
            'type' => AppointmentType::Consultation,
        ]);
    }

    public function test_super_admin_can_view_calendar(): void
    {
        $admin = User::where('email', 'admin@docta.com')->firstOrFail();

        $this->actingAs($admin)->get('/admin/appointments-calendar')->assertOk();
    }

    public function test_calendar_displays_appointments_of_the_month(): void
    {
        $admin = User::where('email', 'admin@docta.com')->firstOrFail();
        $doctor = Doctor::factory()->create(['status' => 'active']);
        $patient = Patient::factory()->create(['first_name' => 'PATIENT_UNIQUE_A', 'last_name' => 'AAA']);

        $this->createAppointment($patient, $doctor);

        Livewire::actingAs($admin)
            ->test(AppointmentsCalendar::class)
            ->assertOk()
            ->assertSee('PATIENT_UNIQUE_A');
    }

    public function test_calendar_filters_appointments_by_doctor(): void
    {
        $admin = User::where('email', 'admin@docta.com')->firstOrFail();

        $doctorA = Doctor::factory()->create(['status' => 'active']);
        $doctorB = Doctor::factory()->create(['status' => 'active']);

        $patientA = Patient::factory()->create(['first_name' => 'PATIENT_UNIQUE_A', 'last_name' => 'AAA']);
        $patientB = Patient::factory()->create(['first_name' => 'PATIENT_UNIQUE_B', 'last_name' => 'BBB']);

        $this->createAppointment($patientA, $doctorA);
        $this->createAppointment($patientB, $doctorB);

        $component = Livewire::actingAs($admin)->test(AppointmentsCalendar::class);

        $component->assertSee('PATIENT_UNIQUE_A');
        $component->assertSee('PATIENT_UNIQUE_B');

        $component->set('doctorId', $doctorA->id);
        $component->assertSee('PATIENT_UNIQUE_A');
        $component->assertDontSee('PATIENT_UNIQUE_B');

        $component->set('doctorId', $doctorB->id);
        $component->assertSee('PATIENT_UNIQUE_B');
        $component->assertDontSee('PATIENT_UNIQUE_A');

        $component->set('doctorId', null);
        $component->assertSee('PATIENT_UNIQUE_A');
        $component->assertSee('PATIENT_UNIQUE_B');
    }

    public function test_doctor_user_sees_only_their_appointments_without_filter(): void
    {
        $doctorUser = User::factory()->create();
        $doctorUser->assignRole('doctor');

        $doctor = Doctor::factory()->create(['status' => 'active', 'user_id' => $doctorUser->id]);
        $otherDoctor = Doctor::factory()->create(['status' => 'active']);

        $patientA = Patient::factory()->create(['first_name' => 'PATIENT_UNIQUE_A', 'last_name' => 'AAA']);
        $patientB = Patient::factory()->create(['first_name' => 'PATIENT_UNIQUE_B', 'last_name' => 'BBB']);

        $this->createAppointment($patientA, $doctor);
        $this->createAppointment($patientB, $otherDoctor);

        $component = Livewire::actingAs($doctorUser)->test(AppointmentsCalendar::class);

        $component->assertOk();
        $component->assertDontSee('Tous les médecins');
        $component->assertSee('PATIENT_UNIQUE_A');
        $component->assertDontSee('PATIENT_UNIQUE_B');
    }
}
