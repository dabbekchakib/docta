<?php

namespace App\Services;

use App\Enums\JournalEntryStatus;
use App\Models\AccountingAccount;
use App\Models\CreditNote;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Payment;
use App\Models\Refund;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Calculs du grand livre et de la balance de vérification à partir des
 * écritures validées du journal.
 */
class AccountingBalanceService
{
    /**
     * Lignes d'écriture validées d'une période, avec compte et écriture.
     *
     * @return Collection<int, JournalEntryLine>
     */
    public function postedLines(?Carbon $from = null, ?Carbon $to = null, ?AccountingAccount $account = null): Collection
    {
        $query = JournalEntryLine::query()
            ->select('journal_entry_lines.*')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->with(['account', 'journalEntry' => fn ($q) => $q->withTrashed()->with('source')])
            ->where('journal_entries.status', JournalEntryStatus::Posted->value)
            ->whereNull('journal_entries.deleted_at');

        if ($from) {
            $query->whereDate('journal_entries.entry_date', '>=', $from->toDateString());
        }

        if ($to) {
            $query->whereDate('journal_entries.entry_date', '<=', $to->toDateString());
        }

        if ($account) {
            $query->where('journal_entry_lines.accounting_account_id', $account->id);
        }

        return $query->orderBy('journal_entries.entry_date', 'asc')
            ->orderBy('journal_entries.id', 'asc')
            ->orderBy('journal_entry_lines.id', 'asc')
            ->get();
    }

    /**
     * Balance de vérification : pour chaque compte, total débit, total crédit
     * et solde net orienté selon la nature du compte.
     *
     * @return Collection<int, array{
     *     account: AccountingAccount,
     *     debit: string,
     *     credit: string,
     *     balance: string,
     *     balance_side: string
     * }>
     */
    public function trialBalance(?Carbon $from = null, ?Carbon $to = null): Collection
    {
        $lines = $this->postedLines($from, $to);

        $totals = $lines->groupBy('accounting_account_id');

        $result = collect();

        foreach ($totals as $accountId => $accountLines) {
            /** @var AccountingAccount|null $account */
            $account = $accountLines->first()?->account;

            if (! $account) {
                continue;
            }

            $debit = Money::normalize((string) $accountLines->sum('debit'));
            $credit = Money::normalize((string) $accountLines->sum('credit'));

            $balance = Money::sub($debit, $credit);
            $isDebitSide = $account->normal_balance === 'debit';

            $result->push([
                'account' => $account,
                'debit' => $debit,
                'credit' => $credit,
                'balance' => $isDebitSide
                    ? Money::normalize($balance)
                    : Money::normalize(Money::sub($credit, $debit)),
                'balance_side' => $isDebitSide ? 'debit' : 'credit',
            ]);
        }

        return $result->sortBy(fn (array $row): string => $row['account']->code)->values();
    }

    /**
     * Grand livre d'un compte : écritures avec solde cumulé, totaux de période
     * et solde de clôture (orienté selon la nature du compte).
     *
     * @return array{
     *     account: AccountingAccount,
     *     opening_balance: string,
     *     opening_side: string,
     *     closing_balance: string,
     *     closing_side: string,
     *     total_debit: string,
     *     total_credit: string,
     *     line_count: int,
     *     lines: Collection<int, array{
     *         entry_id: int,
     *         date: string,
     *         entry_number: string,
     *         description: ?string,
     *         type_label: string,
     *         type_color: string,
     *         source_type: ?string,
     *         source_id: ?int,
     *         source_reference: ?string,
     *         debit: string,
     *         credit: string,
     *         balance: string,
     *         balance_side: string
     *     }>
     * }
     */
    public function accountLedger(AccountingAccount $account, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $openingSigned = Money::zero();

        if ($from) {
            $openingSigned = Money::normalize((string) JournalEntryLine::query()
                ->where('accounting_account_id', $account->id)
                ->whereHas('journalEntry', fn ($q) => $q
                    ->where('status', JournalEntryStatus::Posted->value)
                    ->whereDate('entry_date', '<', $from->toDateString()))
                ->get()
                ->sum(fn (JournalEntryLine $line): float => (float) $line->debit - (float) $line->credit));
        }

        $lines = $this->postedLines($from, $to, $account);

        $isDebitSide = $account->normal_balance === 'debit';

        $totalDebit = Money::normalize((string) $lines->sum('debit'));
        $totalCredit = Money::normalize((string) $lines->sum('credit'));

        $running = $openingSigned;

        $ledgerLines = $lines->map(function (JournalEntryLine $line) use (&$running, $isDebitSide): array {
            $running = Money::add($running, Money::normalize((string) ((float) $line->debit - (float) $line->credit)));

            $entry = $line->journalEntry;
            $source = $entry?->source;

            return [
                'entry_id' => $entry?->id ?? 0,
                'date' => $entry?->entry_date?->format('d/m/Y') ?? '—',
                'entry_number' => $entry?->entry_number ?? '—',
                'description' => $entry?->description,
                'type_label' => $entry?->type?->getLabel() ?? '—',
                'type_color' => $entry?->type?->getColor() ?? 'gray',
                'source_type' => $entry?->source_type,
                'source_id' => $entry?->source_id,
                'source_reference' => $this->sourceReference($source),
                'debit' => (string) $line->debit,
                'credit' => (string) $line->credit,
                'balance' => $isDebitSide
                    ? $running
                    : Money::normalize(Money::sub('0', $running)),
                'balance_side' => $isDebitSide ? 'debit' : 'credit',
            ];
        });

        $closingSigned = Money::add($openingSigned, Money::sub($totalDebit, $totalCredit));

        return [
            'account' => $account,
            'opening_balance' => $isDebitSide
                ? $openingSigned
                : Money::normalize(Money::sub('0', $openingSigned)),
            'opening_side' => $isDebitSide ? 'debit' : 'credit',
            'closing_balance' => $isDebitSide
                ? $closingSigned
                : Money::normalize(Money::sub('0', $closingSigned)),
            'closing_side' => $isDebitSide ? 'debit' : 'credit',
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'line_count' => $lines->count(),
            'lines' => $ledgerLines,
        ];
    }

    /**
     * Référence lisible du document source d'une écriture (facture, paiement…).
     */
    private function sourceReference(?Model $source): ?string
    {
        if (! $source) {
            return null;
        }

        return match (true) {
            $source instanceof Invoice => $source->invoice_number,
            $source instanceof Payment => $source->payment_number,
            $source instanceof CreditNote => $source->credit_note_number,
            $source instanceof Refund => $source->refund_number,
            default => null,
        };
    }

    /**
     * Solde net de tous les comptes à une date (pour soldes d'ouverture).
     *
     * @return array<int, string>  clé = id du compte, valeur = solde signé (débits - crédits)
     */
    public function balancesBefore(?Carbon $date): array
    {
        if (! $date) {
            return [];
        }

        $lines = JournalEntryLine::query()
            ->whereHas('journalEntry', fn ($q) => $q
                ->where('status', JournalEntryStatus::Posted->value)
                ->whereDate('entry_date', '<', $date->toDateString()))
            ->get();

        $balances = [];

        foreach ($lines as $line) {
            $balances[$line->accounting_account_id] = Money::add(
                $balances[$line->accounting_account_id] ?? Money::zero(),
                Money::normalize((string) ((float) $line->debit - (float) $line->credit)),
            );
        }

        return $balances;
    }

    public function totalDebit(?Carbon $from = null, ?Carbon $to = null): string
    {
        return Money::normalize((string) $this->postedLines($from, $to)->sum('debit'));
    }

    public function totalCredit(?Carbon $from = null, ?Carbon $to = null): string
    {
        return Money::normalize((string) $this->postedLines($from, $to)->sum('credit'));
    }

    public function totalEntries(?Carbon $from = null, ?Carbon $to = null): int
    {
        $query = JournalEntry::query()->where('status', JournalEntryStatus::Posted->value);

        if ($from) {
            $query->whereDate('entry_date', '>=', $from->toDateString());
        }

        if ($to) {
            $query->whereDate('entry_date', '<=', $to->toDateString());
        }

        return $query->count();
    }
}
