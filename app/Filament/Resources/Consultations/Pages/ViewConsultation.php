<?php

namespace App\Filament\Resources\Consultations\Pages;

use App\Filament\Resources\Consultations\Actions\CancelConsultationAction;
use App\Filament\Resources\Consultations\Actions\CompleteConsultationAction;
use App\Filament\Resources\Consultations\Actions\PrintConsultationAction;
use App\Filament\Resources\Consultations\ConsultationResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
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
            CompleteConsultationAction::make(),
            CancelConsultationAction::make(),
            PrintConsultationAction::make(),
            EditAction::make(),
            DeleteAction::make(),
        ];
    }
}
