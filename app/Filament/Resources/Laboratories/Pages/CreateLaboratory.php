<?php

namespace App\Filament\Resources\Laboratories\Pages;

use App\Filament\Resources\Laboratories\LaboratoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLaboratory extends CreateRecord
{
    protected static string $resource = LaboratoryResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
