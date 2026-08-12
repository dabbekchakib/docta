<?php

namespace App\Filament\Resources\LaboratoryRequests\Actions;

use App\Filament\Resources\LaboratoryReports\LaboratoryReportResource;
use App\Models\LaboratoryReport;
use App\Services\LaboratoryReportService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GenerateReportAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'generateLaboratoryReport')
            ->label('Générer le compte rendu')
            ->icon(Heroicon::OutlinedDocumentArrowDown)
            ->color('primary')
            ->requiresConfirmation()
            ->modalHeading('Générer le compte rendu d\'analyses')
            ->modalDescription('Le compte rendu PDF sera généré à partir des résultats validés.')
            ->authorize('create', LaboratoryReport::class)
            ->visible(fn (Action $action): bool => self::canGenerate($action))
            ->action(function (Action $action): RedirectResponse|StreamedResponse|null {
                $report = app(LaboratoryReportService::class)->generate($action->getRecord());

                Notification::make()
                    ->title('Compte rendu généré')
                    ->success()
                    ->send();

                return redirect(LaboratoryReportResource::getUrl('view', ['record' => $report]));
            });
    }

    private static function canGenerate(Action $action): bool
    {
        $request = $action->getRecord();

        if (! $request || ! $request->isValidated()) {
            return false;
        }

        if (! auth()->user()?->can('create', LaboratoryReport::class)) {
            return false;
        }

        return $request->report === null;
    }
}
