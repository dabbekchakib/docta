<?php

namespace App\Filament\Resources\LaboratoryRequests\Pages;

use App\Filament\Resources\Consultations\ConsultationResource;
use App\Filament\Resources\LaboratoryRequests\Actions\AcceptLaboratoryRequestAction;
use App\Filament\Resources\LaboratoryRequests\Actions\CancelLaboratoryRequestAction;
use App\Filament\Resources\LaboratoryRequests\Actions\CollectSampleAction;
use App\Filament\Resources\LaboratoryRequests\Actions\DownloadReportAction;
use App\Filament\Resources\LaboratoryRequests\Actions\EnterResultsAction;
use App\Filament\Resources\LaboratoryRequests\Actions\GenerateReportAction;
use App\Filament\Resources\LaboratoryRequests\Actions\ReceiveSamplesAction;
use App\Filament\Resources\LaboratoryRequests\Actions\RejectSampleAction;
use App\Filament\Resources\LaboratoryRequests\Actions\SubmitLaboratoryRequestAction;
use App\Filament\Resources\LaboratoryRequests\Actions\ValidateResultsAction;
use App\Filament\Resources\LaboratoryRequests\LaboratoryRequestResource;
use App\Filament\Resources\MedicalRecords\MedicalRecordResource;
use App\Models\LaboratoryRequest;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class ViewLaboratoryRequest extends ViewRecord
{
    protected static string $resource = LaboratoryRequestResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        activity('laboratory_requests')
            ->performedOn($this->record)
            ->causedBy(Auth::user())
            ->log('Fiche demande d\'examen consultée');
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
            Action::make('openConsultation')
                ->label('Voir la consultation')
                ->icon(Heroicon::OutlinedClipboardDocument)
                ->color('gray')
                ->visible(fn (): bool => $this->record instanceof LaboratoryRequest
                    && $this->record->consultation
                    && (auth()->user()?->can('view', $this->record->consultation) ?? false))
                ->url(fn (): string => ConsultationResource::getUrl('view', ['record' => $this->record->consultation])),
            Action::make('openMedicalRecord')
                ->label('Voir le dossier médical')
                ->icon(Heroicon::OutlinedFolder)
                ->color('gray')
                ->visible(fn (): bool => $this->record instanceof LaboratoryRequest
                    && $this->record->medicalRecord()
                    && (auth()->user()?->can('view', $this->record->medicalRecord()) ?? false))
                ->url(fn (): string => MedicalRecordResource::getUrl('view', ['record' => $this->record->medicalRecord()])),
            SubmitLaboratoryRequestAction::make(),
            AcceptLaboratoryRequestAction::make(),
            CollectSampleAction::make(),
            ReceiveSamplesAction::make(),
            RejectSampleAction::make(),
            EnterResultsAction::make(),
            ValidateResultsAction::make(),
            GenerateReportAction::make(),
            DownloadReportAction::make(),
            EditAction::make(),
            DeleteAction::make(),
        ];
    }
}
