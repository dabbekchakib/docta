<?php

namespace App\Filament\Resources\Patients\Pages;

use App\Filament\Resources\Invoices\InvoiceResource;
use App\Filament\Resources\MedicalRecords\MedicalRecordResource;
use App\Filament\Resources\Patients\PatientResource;
use App\Filament\Resources\Prescriptions\PrescriptionResource;
use App\Models\Invoice;
use App\Models\Prescription;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class ViewPatient extends ViewRecord
{
    protected static string $resource = PatientResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        activity('patients')
            ->performedOn($this->record)
            ->causedBy(Auth::user())
            ->log('Fiche patient consultée');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('createInvoice')
                ->label('Créer une facture')
                ->icon(Heroicon::OutlinedReceiptPercent)
                ->color('primary')
                ->visible(fn (): bool => auth()->user()?->can('create', Invoice::class) ?? false)
                ->url(fn (): string => InvoiceResource::getUrl('create', ['patient' => $this->record->id])),
            Action::make('createPrescription')
                ->label('Créer une ordonnance')
                ->icon(Heroicon::OutlinedClipboardDocumentList)
                ->color('primary')
                ->visible(fn (): bool => auth()->user()?->can('create', Prescription::class) ?? false)
                ->url(fn (): string => PrescriptionResource::getUrl('create', ['patient' => $this->record->id])),
            Action::make('openMedicalRecord')
                ->label('Ouvrir le dossier médical')
                ->icon(Heroicon::OutlinedFolder)
                ->color('primary')
                ->visible(fn (): bool => auth()->user()?->can('view', $this->record->medicalRecord) ?? false)
                ->url(fn (): string => MedicalRecordResource::getUrl('view', ['record' => $this->record->medicalRecord])),
            EditAction::make(),
            DeleteAction::make(),
        ];
    }
}
