<?php

namespace Tests\Feature;

use App\Filament\Resources\Consultations\Pages\CreateConsultation;
use App\Filament\Resources\Consultations\Pages\EditConsultation;
use App\Filament\Resources\Consultations\Pages\ListConsultations;
use App\Filament\Resources\Consultations\Pages\ViewConsultation;
use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class ConsultationResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    protected function admin(): User
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        return $admin;
    }

    public function test_admin_can_open_consultations_list_page(): void
    {
        $consultation = Consultation::factory()->create([
            'consultation_date' => now()->toDateString(),
        ]);

        Livewire::actingAs($this->admin())
            ->test(ListConsultations::class)
            ->assertSuccessful()
            ->assertSee($consultation->consultation_number);
    }

    public function test_admin_can_create_a_consultation_through_the_form(): void
    {
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();

        Livewire::actingAs($this->admin())
            ->test(CreateConsultation::class)
            ->fillForm([
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'consultation_date' => today()->toDateString(),
                'type' => 'first_visit',
                'status' => 'in_progress',
                'reason' => 'Douleurs abdominales',
                'diagnosis' => '<p>Gastro-entérite aiguë</p>',
                'vitalSign' => [
                    [
                        'temperature' => 37.2,
                        'weight' => 70,
                        'height' => 175,
                        'blood_pressure' => '120/80',
                        'heart_rate' => 78,
                    ],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $consultation = Consultation::where('patient_id', $patient->id)->firstOrFail();

        $this->assertSame('CONS-000001', $consultation->consultation_number);
        $this->assertSame('Douleurs abdominales', $consultation->reason);
        $this->assertSame('in_progress', $consultation->status->value);
        $this->assertSame('first_visit', $consultation->type->value);
        $this->assertDatabaseHas('vital_signs', [
            'consultation_id' => $consultation->id,
            'weight' => 70,
            'height' => 175,
        ]);
        $this->assertNotNull($consultation->vitalSign->bmi);
    }

    public function test_create_form_validates_required_fields(): void
    {
        Livewire::actingAs($this->admin())
            ->test(CreateConsultation::class)
            ->fillForm([])
            ->call('create')
            ->assertHasFormErrors(['patient_id', 'doctor_id', 'consultation_date', 'type']);
    }

    public function test_admin_can_update_a_consultation(): void
    {
        $consultation = Consultation::factory()->create([
            'consultation_date' => now()->toDateString(),
            'medical_notes' => 'Avant',
        ]);

        Livewire::actingAs($this->admin())
            ->test(EditConsultation::class, ['record' => $consultation->getKey()])
            ->fillForm([
                'medical_notes' => 'Après',
                'consultation_number' => $consultation->consultation_number,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('consultations', [
            'id' => $consultation->id,
            'medical_notes' => '<p>Après</p>',
        ]);
    }

    public function test_admin_can_open_consultation_view_page(): void
    {
        $consultation = Consultation::factory()->create([
            'consultation_date' => now()->toDateString(),
        ]);

        Livewire::actingAs($this->admin())
            ->test(ViewConsultation::class, ['record' => $consultation->getKey()])
            ->assertSuccessful()
            ->assertSee($consultation->consultation_number);
    }

    public function test_print_action_downloads_consultation_pdf(): void
    {
        $consultation = Consultation::factory()->create([
            'consultation_date' => now()->toDateString(),
        ]);

        Livewire::actingAs($this->admin())
            ->test(ViewConsultation::class, ['record' => $consultation->getKey()])
            ->callAction('printConsultation')
            ->assertFileDownloaded('consultation-CONS-000001.pdf', null, 'application/pdf');
    }

    public function test_doctor_sees_only_his_own_consultations_on_list(): void
    {
        $doctorUser = User::factory()->create();
        $doctorUser->assignRole('doctor');
        $doctor = Doctor::factory()->create(['user_id' => $doctorUser->id]);
        $own = Consultation::factory()->create([
            'doctor_id' => $doctor->id,
            'consultation_date' => now()->toDateString(),
        ]);
        $other = Consultation::factory()->create([
            'consultation_date' => now()->toDateString(),
        ]);

        Livewire::actingAs($doctorUser)
            ->test(ListConsultations::class)
            ->assertSuccessful()
            ->assertSee($own->consultation_number)
            ->assertDontSee($other->consultation_number);
    }

    public function test_doctor_can_open_edit_page_for_his_own_consultation(): void
    {
        [$doctorUser, $doctor] = $this->doctorWithUser();
        $own = Consultation::factory()->create([
            'doctor_id' => $doctor->id,
            'consultation_date' => now()->toDateString(),
        ]);

        Livewire::actingAs($doctorUser)
            ->test(EditConsultation::class, ['record' => $own->getKey()])
            ->assertSuccessful()
            ->assertSee($own->consultation_number);
    }

    public function test_doctor_cannot_open_edit_page_for_another_doctors_consultation(): void
    {
        [$doctorUser] = $this->doctorWithUser();
        $other = Consultation::factory()->create([
            'consultation_date' => now()->toDateString(),
        ]);

        $this->expectException(ModelNotFoundException::class);

        Livewire::actingAs($doctorUser)
            ->test(EditConsultation::class, ['record' => $other->getKey()]);
    }

    protected function doctorWithUser(): array
    {
        $user = User::factory()->create();
        $user->assignRole('doctor');
        $doctor = Doctor::factory()->create(['user_id' => $user->id]);

        return [$user, $doctor];
    }
}
