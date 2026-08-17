<?php

namespace App\Filament\Pages;

use App\Enums\Permission;
use App\Models\AccountingAccount;
use App\Models\CreditNote;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Refund;
use App\Services\AccountingBalanceService;
use BackedEnum;
use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Route;

class GeneralLedger extends Page
{
    protected string $view = 'filament.pages.general-ledger';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedListBullet;

    protected static string|\UnitEnum|null $navigationGroup = 'Rapports et tableaux de bord';

    protected static ?string $navigationLabel = 'Grand livre';

    protected static ?string $title = 'Grand livre';

    protected static ?int $navigationSort = 5;

    public ?int $accountId = null;

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
     * Comptes groupés par catégorie pour un rendu <optgroup>.
     *
     * @return array<string, array<int, array{id: int, label: string}>>
     */
    public function groupedAccounts(): array
    {
        $groups = [
            'Actif' => [],
            'Passif' => [],
            'Capitaux propres' => [],
            'Produits' => [],
            'Charges' => [],
        ];

        AccountingAccount::query()
            ->orderBy('code')
            ->get()
            ->each(function (AccountingAccount $account) use (&$groups): void {
                $groups[$account->type?->label() ?? 'Actif'][] = [
                    'id' => $account->id,
                    'label' => $account->label(),
                ];
            });

        return array_filter($groups, fn (array $items): bool => $items !== []);
    }

    public function selectedAccount(): ?AccountingAccount
    {
        return $this->accountId ? AccountingAccount::query()->find($this->accountId) : null;
    }

    /**
     * @return array{
     *     account: ?AccountingAccount,
     *     opening_balance: ?string,
     *     opening_side: ?string,
     *     closing_balance: ?string,
     *     closing_side: ?string,
     *     total_debit: ?string,
     *     total_credit: ?string,
     *     line_count: int,
     *     lines: \Illuminate\Support\Collection<int, array<string, mixed>>
     * }
     */
    public function getLedgerData(): array
    {
        $account = $this->selectedAccount();

        if (! $account) {
            return [
                'account' => null,
                'opening_balance' => null,
                'opening_side' => null,
                'closing_balance' => null,
                'closing_side' => null,
                'total_debit' => null,
                'total_credit' => null,
                'line_count' => 0,
                'lines' => collect(),
            ];
        }

        return app(AccountingBalanceService::class)->accountLedger(
            $account,
            $this->from ? Carbon::parse($this->from) : null,
            $this->to ? Carbon::parse($this->to) : null,
        );
    }

    public function invalidRange(): bool
    {
        return $this->from && $this->to && Carbon::parse($this->from)->gt(Carbon::parse($this->to));
    }

    /**
     * URL d'accès au document source d'une écriture, si l'utilisateur peut le consulter.
     */
    public function sourceUrl(?string $sourceType, ?int $sourceId): ?string
    {
        if (! $sourceType || ! $sourceId) {
            return null;
        }

        return match ($sourceType) {
            (new Invoice)->getMorphClass() => $this->resourceUrl('invoices', $sourceId),
            (new Payment)->getMorphClass() => $this->resourceUrl('payments', $sourceId),
            (new CreditNote)->getMorphClass() => $this->resourceUrl('credit-notes', $sourceId),
            (new Refund)->getMorphClass() => $this->resourceUrl('refunds', $sourceId),
            default => null,
        };
    }

    private function resourceUrl(string $slug, int $record): ?string
    {
        $route = "filament.admin.resources.{$slug}.view";

        if (! Route::has($route)) {
            return null;
        }

        return route($route, $record);
    }

    public function resetFilters(): void
    {
        $this->reset(['accountId', 'from', 'to']);
    }
}
