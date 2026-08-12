<?php

namespace Tests\Feature;

use App\Accounting\AccountCode;
use App\Enums\JournalEntryType;
use App\Models\AccountingAccount;
use App\Services\AccountingBalanceService;
use App\Services\JournalEntryService;
use Carbon\Carbon;
use Database\Seeders\AccountingPlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountingBalanceServiceTest extends TestCase
{
    use RefreshDatabase;

    private AccountingBalanceService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(AccountingPlanSeeder::class);
        $this->service = app(AccountingBalanceService::class);
    }

    private function postEntry(string $date, int $debit, int $credit): void
    {
        app(JournalEntryService::class)->post(
            JournalEntryType::Manual->value,
            "Écriture du {$date}",
            [
                ['account_code' => AccountCode::CASH, 'debit' => $debit],
                ['account_code' => AccountCode::REVENUE, 'credit' => $credit],
            ],
            $date,
        );
    }

    public function test_account_ledger_returns_period_totals_and_closing_balance(): void
    {
        $this->postEntry('2026-01-10', 100, 100);
        $this->postEntry('2026-02-20', 250, 250);
        $this->postEntry('2026-03-05', 50, 50);

        $account = AccountingAccount::where('code', AccountCode::CASH)->firstOrFail();

        $ledger = $this->service->accountLedger(
            $account,
            Carbon::parse('2026-02-01'),
            Carbon::parse('2026-02-28'),
        );

        $this->assertSame('100', $ledger['opening_balance']);
        $this->assertSame('debit', $ledger['opening_side']);
        $this->assertSame('250', $ledger['total_debit']);
        $this->assertSame('0.000', $ledger['total_credit']);
        $this->assertSame('350', $ledger['closing_balance']);
        $this->assertSame('debit', $ledger['closing_side']);
        $this->assertCount(1, $ledger['lines']);
        $this->assertSame('350', $ledger['lines']->last()['balance']);
    }

    public function test_account_ledger_is_empty_when_no_activity(): void
    {
        $account = AccountingAccount::where('code', AccountCode::CASH)->firstOrFail();

        $ledger = $this->service->accountLedger($account);

        $this->assertSame('0.000', $ledger['opening_balance']);
        $this->assertSame('0.000', $ledger['total_debit']);
        $this->assertSame('0.000', $ledger['total_credit']);
        $this->assertSame('0.000', $ledger['closing_balance']);
        $this->assertSame(0, $ledger['line_count']);
    }

    public function test_credit_account_balance_is_credit_oriented(): void
    {
        $this->postEntry('2026-01-10', 100, 100);

        $account = AccountingAccount::where('code', AccountCode::REVENUE)->firstOrFail();

        $ledger = $this->service->accountLedger($account);

        $this->assertSame('credit', $ledger['closing_side']);
        $this->assertSame('100', $ledger['closing_balance']);
        $this->assertSame('100', $ledger['total_credit']);
    }
}
