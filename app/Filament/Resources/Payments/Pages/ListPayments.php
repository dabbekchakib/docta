<?php

namespace App\Filament\Resources\Payments\Pages;

use App\Filament\Resources\Payments\PaymentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ListPayments extends ListRecords
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('+ Ajouter un paiement'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Tous'),
            'pending' => Tab::make('En attente')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'pending')),
            'completed' => Tab::make('Encaissés')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'completed')),
            'cancelled' => Tab::make('Annulés')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'cancelled')),
            'refunded' => Tab::make('Remboursés')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'refunded')),
        ];
    }
}
