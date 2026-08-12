<?php

namespace App\Filament\Resources\Invoices\Pages;

use App\Filament\Resources\Invoices\InvoiceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ListInvoices extends ListRecords
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('+ Ajouter une facture'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Toutes'),
            'draft' => Tab::make('Brouillons')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'draft')),
            'issued' => Tab::make('Émises')
                ->modifyQueryUsing(fn ($query) => $query->whereIn('status', ['issued', 'partially_paid'])),
            'paid' => Tab::make('Payées')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'paid')),
            'overdue' => Tab::make('En retard')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'overdue')),
        ];
    }
}
