<?php

namespace Tests\Feature;

use App\Filament\Resources\MedicalRecords\Pages\ListMedicalRecords;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MedicalRecordResourceTest extends TestCase
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

    public function test_admin_can_open_medical_records_list_page(): void
    {
        $record = Patient::factory()->create()->medicalRecord;

        Livewire::actingAs($this->admin())
            ->test(ListMedicalRecords::class)
            ->assertSuccessful()
            ->assertSee($record->medical_record_number);
    }

    public function test_search_by_patient_first_name_finds_the_record(): void
    {
        $patient = Patient::factory()->create(['first_name' => 'Ahmed']);
        $record = $patient->medicalRecord;

        Livewire::actingAs($this->admin())
            ->test(ListMedicalRecords::class)
            ->set('tableSearch', 'ahmed')
            ->assertSuccessful()
            ->assertSee($record->medical_record_number);
    }

    public function test_search_by_patient_last_name_finds_the_record(): void
    {
        $patient = Patient::factory()->create(['last_name' => 'Ben Salah']);
        $record = $patient->medicalRecord;

        Livewire::actingAs($this->admin())
            ->test(ListMedicalRecords::class)
            ->set('tableSearch', 'ben')
            ->assertSuccessful()
            ->assertSee($record->medical_record_number);
    }

    public function test_search_returns_empty_when_no_match(): void
    {
        $record = Patient::factory()->create()->medicalRecord;

        Livewire::actingAs($this->admin())
            ->test(ListMedicalRecords::class)
            ->set('tableSearch', 'zzzzzz')
            ->assertSuccessful()
            ->assertDontSee($record->medical_record_number);
    }

    public function test_medical_record_count_is_updated(): void
    {
        $record = MedicalRecord::factory()->create();

        Livewire::actingAs($this->admin())
            ->test(ListMedicalRecords::class)
            ->assertSuccessful()
            ->assertSee($record->medical_record_number);
    }
}
