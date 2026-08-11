<?php

namespace Tests\Feature;

use App\Enums\PrescriptionStatus;
use App\Filament\Resources\Prescriptions\Pages\CreatePrescription;
use App\Filament\Resources\Prescriptions\Pages\EditPrescription;
use App\Filament\Resources\Prescriptions\Pages\ListPrescriptions;
use App\Filament\Resources\Prescriptions\Pages\ViewPrescription;
use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class PrescriptionResourceTest extends TestCase
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

    protected function prescriptionData(array $overrides = []): array
    {
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();

        return $overrides + [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'prescription_date' => today()->toDateString(),
            'notes' => 'Prendre après les repas',
            'items' => [
                [
                    'medicine_name' => 'Paracétamol',
                    'dosage' => '500 mg',
                    'form' => 'tablet',
                    'route' => 'orale',
                    'frequency' => '3 fois par jour',
                    'duration' => '7',
                    'duration_unit' => 'jour',
                    'quantity' => '20',
                ],
            ],
        ];
    }

    public function test_admin_can_open_prescriptions_list_page(): void
    {
        $prescription = Prescription::factory()->create([
            'patient_id' => Patient::factory(),
            'doctor_id' => Doctor::factory(),
        ]);

        Livewire::actingAs($this->admin())
            ->test(ListPrescriptions::class)
            ->assertSuccessful()
            ->assertSee($prescription->prescription_number);
    }

    public function test_admin_can_create_a_prescription_through_the_form(): void
    {
        Livewire::actingAs($this->admin())
            ->test(CreatePrescription::class)
            ->fillForm($this->prescriptionData())
            ->call('create')
            ->assertHasNoFormErrors();

        $prescription = Prescription::first();

        $this->assertSame('ORD-000001', $prescription->prescription_number);
        $this->assertSame(PrescriptionStatus::Draft, $prescription->status);
        $this->assertSame(1, $prescription->items()->count());
        $this->assertNotNull($prescription->verification_token);
    }

    public function test_create_form_validates_required_fields(): void
    {
        Livewire::actingAs($this->admin())
            ->test(CreatePrescription::class)
            ->fillForm([])
            ->call('create')
            ->assertHasFormErrors(['patient_id', 'doctor_id', 'prescription_date']);
    }

    public function test_create_form_requires_at_least_one_medicine(): void
    {
        $data = $this->prescriptionData(['items' => []]);

        Livewire::actingAs($this->admin())
            ->test(CreatePrescription::class)
            ->fillForm($data)
            ->call('create')
            ->assertHasFormErrors(['items']);
    }

    public function test_create_prescription_prefills_from_consultation(): void
    {
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();
        $consultation = Consultation::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'consultation_date' => now()->toDateString(),
        ]);

        $component = Livewire::actingAs($this->admin())
            ->withQueryParams(['consultation' => $consultation->id])
            ->test(CreatePrescription::class);

        $component->assertSet('data.consultation_id', $consultation->id);
        $component->assertSet('data.patient_id', $patient->id);
        $component->assertSet('data.doctor_id', $doctor->id);
    }

    public function test_admin_can_update_a_draft_prescription(): void
    {
        $prescription = Prescription::factory()->create([
            'patient_id' => Patient::factory(),
            'doctor_id' => Doctor::factory(),
            'status' => PrescriptionStatus::Draft,
            'notes' => 'Avant',
        ]);
        PrescriptionItem::factory()->create(['prescription_id' => $prescription->id]);

        Livewire::actingAs($this->admin())
            ->test(EditPrescription::class, ['record' => $prescription->getKey()])
            ->fillForm([
                'patient_id' => $prescription->patient_id,
                'doctor_id' => $prescription->doctor_id,
                'prescription_date' => today()->toDateString(),
                'notes' => 'Après',
                'items' => [
                    [
                        'medicine_name' => 'Amoxicilline',
                        'dosage' => '1 g',
                        'form' => 'tablet',
                        'route' => 'orale',
                        'frequency' => '2 fois par jour',
                        'duration' => '5',
                        'duration_unit' => 'jour',
                    ],
                ],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $prescription->refresh();

        $this->assertSame('Après', $prescription->notes);
        $this->assertSame(1, $prescription->items()->count());
        $this->assertSame('Amoxicilline', $prescription->items()->first()->medicine_name);
    }

    public function test_admin_can_open_prescription_view_page(): void
    {
        $prescription = Prescription::factory()->create([
            'patient_id' => Patient::factory(),
            'doctor_id' => Doctor::factory(),
        ]);

        Livewire::actingAs($this->admin())
            ->test(ViewPrescription::class, ['record' => $prescription->getKey()])
            ->assertSuccessful()
            ->assertSee($prescription->prescription_number);
    }

    public function test_issue_action_emits_the_prescription(): void
    {
        $prescription = Prescription::factory()->create([
            'patient_id' => Patient::factory(),
            'doctor_id' => Doctor::factory(),
            'status' => PrescriptionStatus::Draft,
        ]);
        PrescriptionItem::factory()->create(['prescription_id' => $prescription->id]);

        Livewire::actingAs($this->admin())
            ->test(ViewPrescription::class, ['record' => $prescription->getKey()])
            ->callAction('issuePrescription')
            ->assertHasNoActionErrors();

        $this->assertSame(PrescriptionStatus::Issued, $prescription->fresh()->status);
    }

    public function test_print_action_downloads_prescription_pdf(): void
    {
        $prescription = Prescription::factory()->create([
            'patient_id' => Patient::factory(),
            'doctor_id' => Doctor::factory(),
        ]);

        Livewire::actingAs($this->admin())
            ->test(ViewPrescription::class, ['record' => $prescription->getKey()])
            ->callAction('printPrescription')
            ->assertFileDownloaded('ordonnance-'.$prescription->prescription_number.'.pdf', null, 'application/pdf');
    }

    public function test_doctor_sees_only_his_own_prescriptions_on_list(): void
    {
        $doctorUser = User::factory()->create();
        $doctorUser->assignRole('doctor');
        $doctor = Doctor::factory()->create(['user_id' => $doctorUser->id]);
        $own = Prescription::factory()->create([
            'doctor_id' => $doctor->id,
            'patient_id' => Patient::factory(),
        ]);
        $other = Prescription::factory()->create([
            'patient_id' => Patient::factory(),
            'doctor_id' => Doctor::factory(),
        ]);

        Livewire::actingAs($doctorUser)
            ->test(ListPrescriptions::class)
            ->assertSuccessful()
            ->assertSee($own->prescription_number)
            ->assertDontSee($other->prescription_number);
    }

    public function test_doctor_cannot_open_edit_page_for_another_doctors_prescription(): void
    {
        [$doctorUser, $doctor] = $this->doctorWithUser();
        $own = Prescription::factory()->create([
            'doctor_id' => $doctor->id,
            'patient_id' => Patient::factory(),
            'status' => PrescriptionStatus::Draft,
        ]);
        $other = Prescription::factory()->create([
            'patient_id' => Patient::factory(),
            'doctor_id' => Doctor::factory(),
            'status' => PrescriptionStatus::Draft,
        ]);

        Livewire::actingAs($doctorUser)
            ->test(EditPrescription::class, ['record' => $own->getKey()])
            ->assertSuccessful();

        $this->expectException(ModelNotFoundException::class);

        Livewire::actingAs($doctorUser)
            ->test(EditPrescription::class, ['record' => $other->getKey()]);
    }

    protected function doctorWithUser(): array
    {
        $user = User::factory()->create();
        $user->assignRole('doctor');
        $doctor = Doctor::factory()->create(['user_id' => $user->id]);

        return [$user, $doctor];
    }
}
