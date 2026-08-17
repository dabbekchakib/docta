<?php

namespace App\Filament\Pages;

use App\Enums\Permission;
use App\Services\FinancialReportPdfService;
use App\Services\FinancialReportService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class FinancialReports extends Page
{
    protected string $view = 'filament.pages.financial-reports';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPresentationChartLine;

    protected static string|\UnitEnum|null $navigationGroup = 'Rapports et tableaux de bord';

    protected static ?string $navigationLabel = 'Rapport financier';

    protected static ?int $navigationSort = 4;

    public ?string $reportDate = null;

    public function mount(): void
    {
        $this->reportDate = now()->format('Y-m-d');
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyPermission([
            Permission::FinancialReportsView->value,
            Permission::CashRegisterView->value,
        ]) ?? false;
    }

    /**
     * Données du rapport (journalier + mensuel + retards).
     *
     * @return array<string, mixed>
     */
    public function getReportData(): array
    {
        $service = app(FinancialReportService::class);

        return [
            'overview' => $service->overview(),
            'daily' => $service->dailyCollection(),
            'monthly' => $service->monthlyRevenue(),
            'overdue' => $service->overdueInvoices(50),
        ];
    }

    public function exportPdf(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return app(FinancialReportPdfService::class)->download();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportPdf')
                ->label('Exporter le rapport PDF')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->color('primary')
                ->action(fn () => $this->exportPdf()),
        ];
    }
}
