<?php

namespace Tests\Feature;

use App\Filament\Resources\Secretaries\Pages\CreateSecretary;
use App\Filament\Resources\Secretaries\Pages\EditSecretary;
use App\Filament\Resources\Secretaries\Pages\ListSecretaries;
use App\Filament\Resources\Secretaries\Pages\ViewSecretary;
use App\Models\Secretary;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SecretaryResourceTest extends TestCase
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

    public function test_admin_can_open_secretaries_list_page(): void
    {
        Secretary::factory()->create();

        Livewire::actingAs($this->admin())
            ->test(ListSecretaries::class)
            ->assertSuccessful()
            ->assertSee('SEC-000001');
    }

    public function test_admin_can_create_a_secretary_through_the_form(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($this->admin())
            ->test(CreateSecretary::class)
            ->fillForm([
                'user_id' => $user->id,
                'first_name' => 'Amira',
                'last_name' => 'Gharbi',
                'gender' => 'female',
                'birth_date' => '1990-03-10',
                'email' => 'amira@example.com',
                'status' => 'active',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('secretaries', [
            'first_name' => 'Amira',
            'last_name' => 'Gharbi',
            'email' => 'amira@example.com',
            'secretary_code' => 'SEC-000001',
            'user_id' => $user->id,
        ]);

        $this->assertTrue($user->fresh()->hasRole('secretary'));
    }

    public function test_create_form_validates_required_fields(): void
    {
        Livewire::actingAs($this->admin())
            ->test(CreateSecretary::class)
            ->fillForm([
                'first_name' => 'Amira',
            ])
            ->call('create')
            ->assertHasFormErrors(['last_name', 'gender']);
    }

    public function test_create_form_validates_unique_email_and_cin(): void
    {
        Secretary::factory()->create([
            'email' => 'dup@example.com',
            'cin' => '12345678',
        ]);

        Livewire::actingAs($this->admin())
            ->test(CreateSecretary::class)
            ->fillForm([
                'first_name' => 'Amira',
                'last_name' => 'Gharbi',
                'gender' => 'female',
                'email' => 'dup@example.com',
                'cin' => '12345678',
                'status' => 'active',
            ])
            ->call('create')
            ->assertHasFormErrors(['email', 'cin']);
    }

    public function test_admin_can_update_a_secretary(): void
    {
        $secretary = Secretary::factory()->create([
            'first_name' => 'Avant',
            'last_name' => 'Modification',
        ]);

        Livewire::actingAs($this->admin())
            ->test(EditSecretary::class, ['record' => $secretary->getKey()])
            ->fillForm([
                'first_name' => 'Après',
                'last_name' => 'Modification',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('secretaries', [
            'id' => $secretary->id,
            'first_name' => 'Après',
            'last_name' => 'Modification',
            'secretary_code' => $secretary->secretary_code,
        ]);
    }

    public function test_doctor_role_cannot_open_create_page(): void
    {
        $doctorUser = User::factory()->create();
        $doctorUser->assignRole('doctor');

        Livewire::actingAs($doctorUser)
            ->test(CreateSecretary::class)
            ->assertForbidden();
    }

    public function test_admin_can_open_secretary_fiche_with_activity_journal(): void
    {
        $admin = $this->admin();
        $secretary = Secretary::factory()->create();

        activity('secretaries')
            ->performedOn($secretary)
            ->causedBy($admin)
            ->log('Fiche secrétaire consultée');

        Livewire::actingAs($admin)
            ->test(ViewSecretary::class, ['record' => $secretary->getKey()])
            ->assertSuccessful()
            ->assertSee('Secrétaire créée')
            ->assertSee($secretary->full_name);
    }
}
