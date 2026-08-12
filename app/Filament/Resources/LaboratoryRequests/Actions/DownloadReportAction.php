<?php

namespace App\Filament\Resources\LaboratoryRequests\Actions;

use App\Models\LaboratoryReport;
use App\Services\LaboratoryReportService;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadReportAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'downloadLaboratoryReport')
            ->label('Télécharger le compte rendu')
            ->icon(Heroicon::OutlinedArrowDownTray)
            ->color('gray')
            ->visible(fn (Action $action): bool => self::canDownload($action))
            ->action(function (Action $action): StreamedResponse {
                /** @var LaboratoryReport $report */
                $report = $action->getRecord()->report;

                return app(LaboratoryReportService::class)->download($report);
            });
    }

    private static function canDownload(Action $action): bool
    {
        $report = $action->getRecord()?->report;

        return $report instanceof LaboratoryReport
            && (auth()->user()?->can('download', $report) ?? false);
    }
}
