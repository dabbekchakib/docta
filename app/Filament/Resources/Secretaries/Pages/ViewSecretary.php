<?php

namespace App\Filament\Resources\Secretaries\Pages;

use App\Filament\Resources\Secretaries\Schemas\SecretaryView;
use App\Filament\Resources\Secretaries\SecretaryResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewSecretary extends ViewRecord
{
    protected static string $resource = SecretaryResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        activity('secretaries')
            ->performedOn($this->record)
            ->causedBy(Auth::user())
            ->log('Fiche secrétaire consultée');
    }

    protected function getHeaderActions(): array
    {
        return [
            ...SecretaryView::resolveAdminActions(),
            EditAction::make(),
        ];
    }
}
