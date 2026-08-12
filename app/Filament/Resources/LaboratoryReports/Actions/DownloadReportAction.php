<?php

namespace App\Filament\Resources\LaboratoryReports\Actions;

use App\Services\LaboratoryReportService;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadReportAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'downloadLaboratoryReport')
            ->label('Télécharger le PDF')
            ->icon(Heroicon::OutlinedArrowDownTray)
            ->color('primary')
            ->authorize('download')
            ->visible(fn (Action $action): bool => auth()->user()?->can('download', $action->getRecord()) ?? false)
            ->action(function (Action $action): StreamedResponse {
                return app(LaboratoryReportService::class)->download($action->getRecord());
            });
    }
}
