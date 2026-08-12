<?php

namespace App\Filament\Resources\LaboratoryReports\Pages;

use App\Filament\Resources\LaboratoryReports\Actions\DownloadReportAction;
use App\Filament\Resources\LaboratoryReports\LaboratoryReportResource;
use App\Filament\Resources\LaboratoryRequests\LaboratoryRequestResource;
use App\Models\LaboratoryReport;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class ViewLaboratoryReport extends ViewRecord
{
    protected static string $resource = LaboratoryReportResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        activity('laboratory_reports')
            ->performedOn($this->record)
            ->causedBy(Auth::user())
            ->log('Fiche compte rendu consultée');
    }

    public function getHeader(): ?View
    {
        return view('filament.pages.stacked-header', [
            'breadcrumbs' => filament()->hasBreadcrumbs() ? $this->getBreadcrumbs() : [],
            'heading' => $this->getHeading(),
            'subheading' => $this->getSubheading(),
            'actions' => $this->getCachedHeaderActions(),
            'actionsAlignment' => $this->getHeaderActionsAlignment(),
            'scopes' => $this->getRenderHookScopes(),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('openRequest')
                ->label('Voir la demande')
                ->icon(Heroicon::OutlinedClipboardDocumentCheck)
                ->color('gray')
                ->visible(fn (): bool => $this->record instanceof LaboratoryReport
                    && $this->record->request
                    && (auth()->user()?->can('view', $this->record->request) ?? false))
                ->url(fn (): string => LaboratoryRequestResource::getUrl('view', ['record' => $this->record->request])),
            DownloadReportAction::make(),
        ];
    }
}
