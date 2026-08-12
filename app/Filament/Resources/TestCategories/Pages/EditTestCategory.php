<?php

namespace App\Filament\Resources\TestCategories\Pages;

use App\Filament\Resources\TestCategories\TestCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTestCategory extends EditRecord
{
    protected static string $resource = TestCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
