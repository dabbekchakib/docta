<?php

namespace Tests\Feature;

use App\Filament\Resources\Doctors\Pages\CreateDoctor;
use App\Filament\Resources\Doctors\Pages\EditDoctor;
use App\Filament\Resources\Doctors\Pages\ListDoctors;
use App\Filament\Resources\Doctors\Pages\ViewDoctor;
use App\Models\Doctor;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DoctorResourceTest extends TestCase
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

    public function test_admin_can_open_doctors_list_page(): void
    {
        Doctor::factory()->create();

        Livewire::actingAs($this->admin())
            ->test(ListDoctors::class)
            ->assertSuccessful()
            ->assertSee('DOC-000001');
    }

    public function test_admin_can_create_a_doctor_through_the_form(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($this->admin())
            ->test(CreateDoctor::class)
            ->fillForm([
                'user_id' => $user->id,
                'first_name' => 'Karim',
                'last_name' => 'Ben Salah',
                'gender' => 'male',
                'birth_date' => '1980-05-12',
                'email' => 'dr.karim@example.com',
                'speciality' => 'Cardiologie',
                'status' => 'active',
                'consultation_fee' => 60,
                'consultation_duration' => 30,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('doctors', [
            'first_name' => 'Karim',
            'last_name' => 'Ben Salah',
            'email' => 'dr.karim@example.com',
            'speciality' => 'Cardiologie',
            'doctor_code' => 'DOC-000001',
        ]);
    }

    public function test_create_form_validates_required_fields(): void
    {
        Livewire::actingAs($this->admin())
            ->test(CreateDoctor::class)
            ->fillForm([
                'first_name' => 'Karim',
            ])
            ->call('create')
            ->assertHasFormErrors(['last_name', 'gender', 'speciality']);
    }

    public function test_create_form_validates_unique_email_and_order_number(): void
    {
        Doctor::factory()->create([
            'email' => 'dup@example.com',
            'order_number' => '12345',
        ]);

        Livewire::actingAs($this->admin())
            ->test(CreateDoctor::class)
            ->fillForm([
                'first_name' => 'Karim',
                'last_name' => 'Ben Salah',
                'gender' => 'male',
                'email' => 'dup@example.com',
                'speciality' => 'Cardiologie',
                'order_number' => '12345',
                'status' => 'active',
            ])
            ->call('create')
            ->assertHasFormErrors(['email', 'order_number']);
    }

    public function test_create_form_validates_consultation_duration_positive(): void
    {
        Livewire::actingAs($this->admin())
            ->test(CreateDoctor::class)
            ->fillForm([
                'first_name' => 'Karim',
                'last_name' => 'Ben Salah',
                'gender' => 'male',
                'speciality' => 'Cardiologie',
                'consultation_duration' => 0,
                'status' => 'active',
            ])
            ->call('create')
            ->assertHasFormErrors(['consultation_duration']);
    }

    public function test_admin_can_update_a_doctor(): void
    {
        $doctor = Doctor::factory()->create([
            'first_name' => 'Avant',
            'last_name' => 'Modification',
        ]);

        Livewire::actingAs($this->admin())
            ->test(EditDoctor::class, ['record' => $doctor->getKey()])
            ->fillForm([
                'first_name' => 'Après',
                'last_name' => 'Modification',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('doctors', [
            'id' => $doctor->id,
            'first_name' => 'Après',
            'last_name' => 'Modification',
            'doctor_code' => $doctor->doctor_code,
        ]);
    }

    public function test_doctor_role_cannot_open_create_page(): void
    {
        $doctorUser = User::factory()->create();
        $doctorUser->assignRole('doctor');

        Livewire::actingAs($doctorUser)
            ->test(CreateDoctor::class)
            ->assertForbidden();
    }

    public function test_admin_can_open_doctor_fiche_with_activity_journal(): void
    {
        $admin = $this->admin();
        $doctor = Doctor::factory()->create();

        activity('doctors')
            ->performedOn($doctor)
            ->causedBy($admin)
            ->log('Fiche médecin consultée');

        Livewire::actingAs($admin)
            ->test(ViewDoctor::class, ['record' => $doctor->getKey()])
            ->assertSuccessful()
            ->assertSee('Médecin créé')
            ->assertSee($doctor->full_name);
    }
}
