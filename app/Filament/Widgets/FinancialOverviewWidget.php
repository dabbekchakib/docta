<?php

namespace App\Filament\Widgets;

use App\Services\FinancialReportService;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FinancialOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected static bool $isLazy = true;

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyPermission([
            'financial_reports.view',
            'invoices.view',
            'payments.view',
        ]) ?? false;
    }

    protected function getStats(): array
    {
        $report = app(FinancialReportService::class);
        $overview = $report->overview();
        $monthly = $report->monthlyRevenue();

        $d = fn (string $value): string => number_format((float) $value, 3, ',', ' ').' DT';

        return [
            Stat::make('Facturé (total)', $d($overview['billed']))
                ->description('Cumul des factures émises')
                ->descriptionIcon(Heroicon::OutlinedReceiptPercent)
                ->icon(Heroicon::OutlinedReceiptPercent)
                ->color('primary'),
            Stat::make('Encaissé (total)', $d($overview['collected']))
                ->description('Total des paiements reçus')
                ->descriptionIcon(Heroicon::OutlinedBanknotes)
                ->icon(Heroicon::OutlinedBanknotes)
                ->color('success'),
            Stat::make('Restant dû', $d($overview['outstanding']))
                ->description($overview['overdue_invoices'].' facture(s) en retard')
                ->descriptionIcon(Heroicon::OutlinedClock)
                ->icon(Heroicon::OutlinedClock)
                ->color('danger'),
            Stat::make('Mois en cours', $d($monthly['billed']))
                ->description('Encaissé : '.$d($monthly['collected']))
                ->descriptionIcon(Heroicon::OutlinedCalendarDays)
                ->icon(Heroicon::OutlinedCalendarDays)
                ->color('warning'),
        ];
    }
}
