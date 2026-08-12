<?php

namespace App\Filament\Resources\Consultations\Pages;

use App\Filament\Resources\Consultations\Actions\CancelConsultationAction;
use App\Filament\Resources\Consultations\Actions\CompleteConsultationAction;
use App\Filament\Resources\Consultations\Actions\PrintConsultationAction;
use App\Filament\Resources\Consultations\ConsultationResource;
use App\Filament\Resources\Invoices\InvoiceResource;
use App\Filament\Resources\LaboratoryRequests\LaboratoryRequestResource;
use App\Filament\Resources\MedicalRecords\MedicalRecordResource;
use App\Filament\Resources\Prescriptions\PrescriptionResource;
use App\Models\Consultation;
use App\Models\Invoice;
use App\Models\LaboratoryRequest;
use App\Models\Prescription;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class ViewConsultation extends ViewRecord
{
    protected static string $resource = ConsultationResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        activity('consultations')
            ->performedOn($this->record)
            ->causedBy(Auth::user())
            ->log('Fiche consultation consultée');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('createInvoice')
                ->label('Créer une facture')
                ->icon(Heroicon::OutlinedReceiptPercent)
                ->color('primary')
                ->visible(fn (): bool => auth()->user()?->can('create', Invoice::class) ?? false)
                ->url(fn (): string => InvoiceResource::getUrl('create', ['consultation' => $this->record->id])),
            Action::make('createPrescription')
                ->label('Créer une ordonnance')
                ->icon(Heroicon::OutlinedClipboardDocumentList)
                ->color('success')
                ->visible(fn (): bool => auth()->user()?->can('create', Prescription::class) ?? false)
                ->url(fn (): string => PrescriptionResource::getUrl('create', ['consultation' => $this->record->id])),
            Action::make('prescribeLaboratory')
                ->label('Prescrire un examen')
                ->icon(Heroicon::OutlinedBeaker)
                ->color('primary')
                ->visible(fn (): bool => auth()->user()?->can('create', LaboratoryRequest::class) ?? false)
                ->url(fn (): string => LaboratoryRequestResource::getUrl('create', ['consultation' => $this->record->id])),
            Action::make('openMedicalRecord')
                ->label('Voir le dossier médical')
                ->icon(Heroicon::OutlinedFolder)
                ->color('gray')
                ->visible(fn (): bool => $this->record instanceof Consultation
                    && $this->record->patient?->medicalRecord
                    && auth()->user()?->can('view', $this->record->patient->medicalRecord) ?? false)
                ->url(fn (): string => MedicalRecordResource::getUrl('view', ['record' => $this->record->patient?->medicalRecord])),
            CompleteConsultationAction::make(),
            CancelConsultationAction::make(),
            PrintConsultationAction::make(),
            EditAction::make(),
            DeleteAction::make(),
        ];
    }
}
