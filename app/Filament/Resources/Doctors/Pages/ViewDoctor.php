<?php

namespace App\Filament\Resources\Doctors\Pages;

use App\Filament\Resources\Doctors\DoctorResource;
use App\Filament\Resources\Prescriptions\PrescriptionResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
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
            Action::make('viewPrescriptions')
                ->label('Voir les ordonnances')
                ->icon(Heroicon::OutlinedClipboardDocumentList)
                ->color('gray')
                ->visible(fn (): bool => auth()->user()?->can('viewAny', \App\Models\Prescription::class) ?? false)
                ->url(fn (): string => PrescriptionResource::getUrl('index', ['tableFilters' => ['doctor_id' => ['value' => $this->record->id]]])),
            EditAction::make(),
            DeleteAction::make(),
        ];
    }
}
