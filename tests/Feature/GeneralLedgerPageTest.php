<?php

namespace Tests\Feature;

use App\Filament\Pages\GeneralLedger;
use App\Models\AccountingAccount;
use App\Models\User;
use Database\Seeders\AccountingPlanSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GeneralLedgerPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(AccountingPlanSeeder::class);
    }

    public function test_super_admin_can_view_general_ledger_page(): void
    {
        $admin = User::where('email', 'admin@docta.com')->firstOrFail();

        $this->actingAs($admin)->get('/admin/general-ledger')->assertOk();
    }

    public function test_accountant_can_view_general_ledger_page(): void
    {
        $accountant = User::factory()->create();
        $accountant->assignRole('accountant');

        $this->actingAs($accountant)->get('/admin/general-ledger')->assertOk();
    }

    public function test_user_without_accounting_permission_cannot_access(): void
    {
        $doctor = User::factory()->create();
        $doctor->assignRole('doctor');

        $this->actingAs($doctor)->get('/admin/general-ledger')->assertForbidden();
    }

    public function test_general_ledger_renders_with_selected_account(): void
    {
        $admin = User::where('email', 'admin@docta.com')->firstOrFail();
        $account = AccountingAccount::where('code', '531')->firstOrFail();

        Livewire::actingAs($admin)
            ->test(GeneralLedger::class)
            ->set('accountId', $account->id)
            ->assertOk()
            ->assertSee('Caisse')
            ->assertSee('Solde de clôture')
            ->assertSee('Solde d\'ouverture', false)
            ->assertSee('Imprimer');
    }
}
