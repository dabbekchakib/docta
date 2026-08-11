<?php

namespace App\Filament\Resources\Consultations\Pages;

use App\Filament\Resources\Consultations\Actions\CancelConsultationAction;
use App\Filament\Resources\Consultations\Actions\CompleteConsultationAction;
use App\Filament\Resources\Consultations\Actions\PrintConsultationAction;
use App\Filament\Resources\Consultations\ConsultationResource;
use App\Filament\Resources\MedicalRecords\MedicalRecordResource;
use App\Models\Consultation;
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
