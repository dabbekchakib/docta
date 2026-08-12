<?php

namespace App\Filament\Resources\TestCategories;

use App\Filament\Resources\TestCategories\Pages\CreateTestCategory;
use App\Filament\Resources\TestCategories\Pages\EditTestCategory;
use App\Filament\Resources\TestCategories\Pages\ListTestCategories;
use App\Filament\Resources\TestCategories\Schemas\TestCategoryForm;
use App\Filament\Resources\TestCategories\Tables\TestCategoriesTable;
use App\Models\TestCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TestCategoryResource extends Resource
{
    protected static ?string $model = TestCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFolderOpen;

    protected static string|\UnitEnum|null $navigationGroup = 'Paramètres';

    protected static ?string $navigationLabel = 'Catégories d\'examens';

    protected static ?string $modelLabel = 'catégorie d\'examen';

    protected static ?string $pluralModelLabel = 'catégories d\'examens';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyPermission([
            'laboratory_tests.view',
            'laboratory_tests.create',
            'laboratory_tests.update',
            'laboratory_tests.delete',
        ]) ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return TestCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TestCategoriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTestCategories::route('/'),
            'create' => CreateTestCategory::route('/create'),
            'edit' => EditTestCategory::route('/{record}/edit'),
        ];
    }
}
