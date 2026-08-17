<?php

namespace App\Filament\Patient\Widgets;

use App\Enums\InvoiceStatus;
use App\Models\Patient;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UnpaidInvoicesWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $patient = $this->getPatient();

        if (! $patient) {
            return [
                Stat::make('Factures impayées', '—')
                    ->description('Aucun patient associé')
                    ->descriptionIcon(Heroicon::OutlinedExclamationTriangle)
                    ->color('gray'),
            ];
        }

        $unpaidInvoices = $patient->invoices()
            ->whereIn('status', [
                InvoiceStatus::Issued->value,
                InvoiceStatus::PartiallyPaid->value,
                InvoiceStatus::Overdue->value,
            ])
            ->get();

        $totalUnpaid = $unpaidInvoices->sum('amount_remaining');
        $countUnpaid = $unpaidInvoices->count();

        return [
            Stat::make('Montant impayé', number_format((float) $totalUnpaid, 3, ',', ' ').' DT')
                ->description("{$countUnpaid} facture(s) en attente")
                ->descriptionIcon(Heroicon::OutlinedBanknotes)
                ->icon(Heroicon::OutlinedCurrencyDollar)
                ->color($countUnpaid > 0 ? 'danger' : 'success'),
        ];
    }

    protected function getPatient(): ?Patient
    {
        return auth()->user()->patient;
    }
}
