<?php

namespace App\Filament\Resources\LaboratoryRequests\Pages;

use App\Filament\Resources\LaboratoryRequests\LaboratoryRequestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLaboratoryRequests extends ListRecords
{
    protected static string $resource = LaboratoryRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
