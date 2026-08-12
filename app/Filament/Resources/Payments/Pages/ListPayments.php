<?php

namespace App\Filament\Resources\Payments\Pages;

use App\Filament\Resources\Payments\PaymentResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ListPayments extends ListRecords
{
    protected static string $resource = PaymentResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Tous'),
            'completed' => Tab::make('Encaissés')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'completed')),
            'pending' => Tab::make('En attente')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'pending')),
            'cancelled' => Tab::make('Annulés')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'cancelled')),
            'refunded' => Tab::make('Remboursés')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'refunded')),
        ];
    }
}
