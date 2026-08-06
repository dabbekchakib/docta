<?php

namespace Tests\Feature;

use App\Filament\Resources\Patients\Pages\CreatePatient;
use App\Filament\Resources\Patients\Pages\EditPatient;
use App\Filament\Resources\Patients\Pages\ListPatients;
use App\Filament\Resources\Patients\Pages\ViewPatient;
use App\Models\Patient;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PatientResourceTest extends TestCase
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

    public function test_admin_can_open_patients_list_page(): void
    {
        Patient::factory()->create(['patient_number' => 'PAT-000001']);

        Livewire::actingAs($this->admin())
            ->test(ListPatients::class)
            ->assertSuccessful()
            ->assertSee('PAT-000001');
    }

    public function test_admin_can_create_a_patient_through_the_form(): void
    {
        Livewire::actingAs($this->admin())
            ->test(CreatePatient::class)
            ->fillForm([
                'title' => 'mr',
                'first_name' => 'Karim',
                'last_name' => 'Ben Salah',
                'gender' => 'male',
                'birth_date' => '1990-05-12',
                'phone' => '+21620123456',
                'email' => 'karim@example.com',
                'status' => 'active',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('patients', [
            'first_name' => 'Karim',
            'last_name' => 'Ben Salah',
            'email' => 'karim@example.com',
            'patient_number' => 'PAT-000001',
        ]);
    }

    public function test_create_form_validates_required_fields(): void
    {
        Livewire::actingAs($this->admin())
            ->test(CreatePatient::class)
            ->fillForm([
                'first_name' => 'Karim',
                'phone' => '+21620123456',
            ])
            ->call('create')
            ->assertHasFormErrors(['last_name', 'gender']);
    }

    public function test_create_form_validates_unique_email(): void
    {
        Patient::factory()->create(['email' => 'dup@example.com']);

        Livewire::actingAs($this->admin())
            ->test(CreatePatient::class)
            ->fillForm([
                'first_name' => 'Karim',
                'last_name' => 'Ben Salah',
                'gender' => 'male',
                'phone' => '+21620123456',
                'email' => 'dup@example.com',
                'status' => 'active',
            ])
            ->call('create')
            ->assertHasFormErrors(['email']);
    }

    public function test_admin_can_update_a_patient(): void
    {
        $patient = Patient::factory()->create([
            'first_name' => 'Avant',
            'last_name' => 'Modification',
        ]);

        Livewire::actingAs($this->admin())
            ->test(EditPatient::class, ['record' => $patient->getKey()])
            ->fillForm([
                'first_name' => 'Après',
                'last_name' => 'Modification',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('patients', [
            'id' => $patient->id,
            'first_name' => 'Après',
            'last_name' => 'Modification',
            'patient_number' => $patient->patient_number,
        ]);
    }

    public function test_doctor_cannot_open_patients_create_page(): void
    {
        $doctor = User::factory()->create();
        $doctor->assignRole('doctor');

        Livewire::actingAs($doctor)
            ->test(CreatePatient::class)
            ->assertForbidden();
    }

    public function test_admin_can_open_patient_fiche_with_activity_journal(): void
    {
        $admin = $this->admin();
        $patient = Patient::factory()->create();

        activity('patients')
            ->performedOn($patient)
            ->causedBy($admin)
            ->log('Fiche patient consultée');

        Livewire::actingAs($admin)
            ->test(ViewPatient::class, ['record' => $patient->getKey()])
            ->assertSuccessful()
            ->assertSee('Patient créé')
            ->assertSee($patient->full_name);
    }
}
