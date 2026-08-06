<?php

namespace Tests\Feature;

use App\Filament\Resources\Appointments\Pages\CreateAppointment;
use App\Filament\Resources\Appointments\Pages\EditAppointment;
use App\Filament\Resources\Appointments\Pages\ListAppointments;
use App\Filament\Resources\Appointments\Pages\ViewAppointment;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class AppointmentResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    protected function admin(): User
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        return $admin;
    }

    public function test_admin_can_open_appointments_list_page(): void
    {
        $appointment = Appointment::factory()->create();

        Livewire::actingAs($this->admin())
            ->test(ListAppointments::class)
            ->assertSuccessful()
            ->assertSee($appointment->appointment_number);
    }

    public function test_admin_can_create_an_appointment_through_the_form(): void
    {
        Notification::fake();

        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();

        Livewire::actingAs($this->admin())
            ->test(CreateAppointment::class)
            ->fillForm([
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'appointment_date' => today()->addDays(3)->toDateString(),
                'start_time' => '09:00',
                'duration' => 30,
                'type' => 'consultation',
                'status' => 'pending',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('appointments', [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => today()->addDays(3)->format('Y-m-d 00:00:00'),
            'status' => 'pending',
            'end_time' => '09:30',
        ]);
    }

    public function test_create_form_validates_required_fields(): void
    {
        Livewire::actingAs($this->admin())
            ->test(CreateAppointment::class)
            ->fillForm([])
            ->call('create')
            ->assertHasFormErrors(['patient_id', 'doctor_id', 'appointment_date', 'start_time']);
    }

    public function test_admin_can_update_an_appointment(): void
    {
        Notification::fake();

        $appointment = Appointment::factory()->create(['notes' => 'Avant']);

        Livewire::actingAs($this->admin())
            ->test(EditAppointment::class, ['record' => $appointment->getKey()])
            ->fillForm([
                'notes' => 'Après',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'notes' => 'Après',
            'appointment_number' => $appointment->appointment_number,
        ]);
    }

    public function test_admin_can_open_appointment_view_page(): void
    {
        $appointment = Appointment::factory()->create();

        Livewire::actingAs($this->admin())
            ->test(ViewAppointment::class, ['record' => $appointment->getKey()])
            ->assertSuccessful()
            ->assertSee($appointment->appointment_number);
    }

    public function test_doctor_sees_only_his_own_appointments_on_list(): void
    {
        Notification::fake();

        $doctorUser = User::factory()->create();
        $doctorUser->assignRole('doctor');
        $doctor = Doctor::factory()->create(['user_id' => $doctorUser->id]);
        $own = Appointment::factory()->create(['doctor_id' => $doctor->id]);
        $other = Appointment::factory()->create();

        Livewire::actingAs($doctorUser)
            ->test(ListAppointments::class)
            ->assertSuccessful()
            ->assertSee($own->appointment_number)
            ->assertDontSee($other->appointment_number);
    }

    public function test_doctor_can_open_create_page(): void
    {
        $doctor = User::factory()->create();
        $doctor->assignRole('doctor');

        Livewire::actingAs($doctor)
            ->test(CreateAppointment::class)
            ->assertSuccessful();
    }
}
