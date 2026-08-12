<?php

namespace App\Filament\Resources\LaboratoryTests\Pages;

use App\Filament\Resources\LaboratoryTests\LaboratoryTestResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLaboratoryTest extends CreateRecord
{
    protected static string $resource = LaboratoryTestResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
