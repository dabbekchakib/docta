<?php

namespace App\Filament\Pages;

use App\Enums\Permission;
use App\Services\AccountingBalanceService;
use BackedEnum;
use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class TrialBalance extends Page
{
    protected string $view = 'filament.pages.trial-balance';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedScale;

    protected static string|\UnitEnum|null $navigationGroup = 'Rapports et tableaux de bord';

    protected static ?string $navigationLabel = 'Balance de vérification';

    protected static ?int $navigationSort = 6;

    public ?string $from = null;

    public ?string $to = null;

    public function mount(): void
    {
        $this->from = now()->startOfYear()->format('Y-m-d');
        $this->to = now()->toDateString();
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasPermissionTo(Permission::AccountingView->value) ?? false;
    }

    /**
     * @return array{
     *     rows: \Illuminate\Support\Collection<int, array<string, mixed>>,
     *     total_debit: string,
     *     total_credit: string,
     *     entries: int
     * }
     */
    public function getBalanceData(): array
    {
        $service = app(AccountingBalanceService::class);
        $from = $this->from ? Carbon::parse($this->from) : null;
        $to = $this->to ? Carbon::parse($this->to) : null;

        return [
            'rows' => $service->trialBalance($from, $to),
            'total_debit' => $service->totalDebit($from, $to),
            'total_credit' => $service->totalCredit($from, $to),
            'entries' => $service->totalEntries($from, $to),
        ];
    }

    public function resetFilters(): void
    {
        $this->reset(['from', 'to']);
    }
}
