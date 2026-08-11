<?php

namespace App\Filament\Resources\Prescriptions\Pages;

use App\Filament\Resources\Consultations\ConsultationResource;
use App\Filament\Resources\MedicalRecords\MedicalRecordResource;
use App\Filament\Resources\Prescriptions\Actions\CancelPrescriptionAction;
use App\Filament\Resources\Prescriptions\Actions\DuplicatePrescriptionAction;
use App\Filament\Resources\Prescriptions\Actions\IssuePrescriptionAction;
use App\Filament\Resources\Prescriptions\Actions\PrintPrescriptionAction;
use App\Filament\Resources\Prescriptions\PrescriptionResource;
use App\Models\Prescription;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class ViewPrescription extends ViewRecord
{
    protected static string $resource = PrescriptionResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        activity('prescriptions')
            ->performedOn($this->record)
            ->causedBy(Auth::user())
            ->log('Fiche ordonnance consultée');
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
                ->visible(fn (): bool => $this->record instanceof Prescription
                    && $this->record->consultation
                    && auth()->user()?->can('view', $this->record->consultation) ?? false)
                ->url(fn (): string => ConsultationResource::getUrl('view', ['record' => $this->record->consultation])),
            Action::make('openMedicalRecord')
                ->label('Voir le dossier médical')
                ->icon(Heroicon::OutlinedFolder)
                ->color('gray')
                ->visible(fn (): bool => $this->record instanceof Prescription
                    && $this->record->medicalRecord()
                    && auth()->user()?->can('view', $this->record->medicalRecord()) ?? false)
                ->url(fn (): string => MedicalRecordResource::getUrl('view', ['record' => $this->record->medicalRecord()])),
            IssuePrescriptionAction::make(),
            CancelPrescriptionAction::make(),
            PrintPrescriptionAction::make(),
            DuplicatePrescriptionAction::make(),
            EditAction::make(),
            DeleteAction::make(),
        ];
    }
}
