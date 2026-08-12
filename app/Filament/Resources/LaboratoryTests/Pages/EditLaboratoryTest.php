<?php

namespace App\Filament\Resources\LaboratoryTests\Pages;

use App\Filament\Resources\LaboratoryTests\LaboratoryTestResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLaboratoryTest extends EditRecord
{
    protected static string $resource = LaboratoryTestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
