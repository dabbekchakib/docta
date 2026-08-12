<?php

namespace App\Filament\Resources\Refunds\Pages;

use App\Filament\Resources\Refunds\Actions\CreateRefundHeaderAction;
use App\Filament\Resources\Refunds\RefundResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ListRefunds extends ListRecords
{
    protected static string $resource = RefundResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateRefundHeaderAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Tous'),
            'pending' => Tab::make('En attente')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'pending')),
            'approved' => Tab::make('Approuvés')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'approved')),
            'completed' => Tab::make('Exécutés')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'completed')),
            'rejected' => Tab::make('Refusés')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'rejected')),
            'cancelled' => Tab::make('Annulés')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'cancelled')),
        ];
    }
}
