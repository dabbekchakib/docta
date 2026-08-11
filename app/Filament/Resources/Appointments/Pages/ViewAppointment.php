<?php

namespace App\Filament\Resources\Appointments\Pages;

use App\Enums\AppointmentStatus;
use App\Filament\Resources\Appointments\Actions\CancelAppointmentAction;
use App\Filament\Resources\Appointments\Actions\ConfirmAppointmentAction;
use App\Filament\Resources\Appointments\AppointmentResource;
use App\Filament\Resources\Consultations\Actions\StartConsultationAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewAppointment extends ViewRecord
{
    protected static string $resource = AppointmentResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        activity('appointments')
            ->performedOn($this->record)
            ->causedBy(Auth::user())
            ->log('Fiche rendez-vous consultée');
    }

    protected function getHeaderActions(): array
    {
        return [
            StartConsultationAction::make(),
            ConfirmAppointmentAction::make(),
            CancelAppointmentAction::make(),
            EditAction::make(),
            DeleteAction::make(),
        ];
    }
}
