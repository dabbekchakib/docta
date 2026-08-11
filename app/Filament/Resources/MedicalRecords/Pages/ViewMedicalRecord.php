<?php

namespace App\Filament\Resources\MedicalRecords\Pages;

use App\Filament\Resources\MedicalRecords\Actions\PrintMedicalRecordAction;
use App\Filament\Resources\MedicalRecords\MedicalRecordResource;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewMedicalRecord extends ViewRecord
{
    protected static string $resource = MedicalRecordResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        activity('medical_records')
            ->performedOn($this->record)
            ->causedBy(Auth::user())
            ->log('Dossier médical consulté');
    }

    protected function getHeaderActions(): array
    {
        return [
            PrintMedicalRecordAction::make(),
        ];
    }
}
