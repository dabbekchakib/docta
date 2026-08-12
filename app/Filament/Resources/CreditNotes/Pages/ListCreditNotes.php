<?php

namespace App\Filament\Resources\CreditNotes\Pages;

use App\Filament\Resources\CreditNotes\Actions\CreateCreditNoteHeaderAction;
use App\Filament\Resources\CreditNotes\CreditNoteResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ListCreditNotes extends ListRecords
{
    protected static string $resource = CreditNoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateCreditNoteHeaderAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Tous'),
            'draft' => Tab::make('Brouillons')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'draft')),
            'issued' => Tab::make('Émis')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'issued')),
            'cancelled' => Tab::make('Annulés')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'cancelled')),
        ];
    }
}
