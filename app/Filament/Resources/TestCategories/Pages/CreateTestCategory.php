<?php

namespace App\Filament\Resources\TestCategories\Pages;

use App\Filament\Resources\TestCategories\TestCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTestCategory extends CreateRecord
{
    protected static string $resource = TestCategoryResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
