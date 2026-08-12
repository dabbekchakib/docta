<?php

namespace App\Filament\Resources\TestCategories\Pages;

use App\Filament\Resources\TestCategories\TestCategoryResource;
use Filament\Resources\Pages\ListRecords;

class ListTestCategories extends ListRecords
{
    protected static string $resource = TestCategoryResource::class;
}
