<?php

namespace App\Filament\Resources\Doctors\Pages;

use App\Filament\Resources\Doctors\DoctorResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewDoctor extends ViewRecord
{
    protected static string $resource = DoctorResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        activity('doctors')
            ->performedOn($this->record)
            ->causedBy(Auth::user())
            ->log('Fiche médecin consultée');
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make(),
        ];
    }
}
