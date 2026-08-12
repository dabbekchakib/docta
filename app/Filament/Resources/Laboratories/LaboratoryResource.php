<?php

namespace App\Filament\Resources\Laboratories;

use App\Filament\Resources\Laboratories\Pages\CreateLaboratory;
use App\Filament\Resources\Laboratories\Pages\EditLaboratory;
use App\Filament\Resources\Laboratories\Pages\ListLaboratories;
use App\Filament\Resources\Laboratories\Schemas\LaboratoryForm;
use App\Filament\Resources\Laboratories\Tables\LaboratoriesTable;
use App\Models\Laboratory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LaboratoryResource extends Resource
{
    protected static ?string $model = Laboratory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingLibrary;

    protected static string|\UnitEnum|null $navigationGroup = 'Paramètres';

    protected static ?string $navigationLabel = 'Laboratoires';

    protected static ?string $modelLabel = 'laboratoire';

    protected static ?string $pluralModelLabel = 'laboratoires';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyPermission([
            'laboratories.view',
            'laboratories.create',
            'laboratories.update',
            'laboratories.delete',
        ]) ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return LaboratoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LaboratoriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLaboratories::route('/'),
            'create' => CreateLaboratory::route('/create'),
            'edit' => EditLaboratory::route('/{record}/edit'),
        ];
    }
}
