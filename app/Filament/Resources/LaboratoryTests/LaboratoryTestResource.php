<?php

namespace App\Filament\Resources\LaboratoryTests;

use App\Filament\Resources\LaboratoryTests\Pages\CreateLaboratoryTest;
use App\Filament\Resources\LaboratoryTests\Pages\EditLaboratoryTest;
use App\Filament\Resources\LaboratoryTests\Pages\ListLaboratoryTests;
use App\Filament\Resources\LaboratoryTests\Pages\ViewLaboratoryTest;
use App\Filament\Resources\LaboratoryTests\RelationManagers\ReferenceRangesRelationManager;
use App\Filament\Resources\LaboratoryTests\Schemas\LaboratoryTestForm;
use App\Filament\Resources\LaboratoryTests\Tables\LaboratoryTestsTable;
use App\Models\LaboratoryTest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LaboratoryTestResource extends Resource
{
    protected static ?string $model = LaboratoryTest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBeaker;

    protected static string|\UnitEnum|null $navigationGroup = 'Paramètres';

    protected static ?string $navigationLabel = 'Examens de laboratoire';

    protected static ?string $modelLabel = 'examen de laboratoire';

    protected static ?string $pluralModelLabel = 'examens de laboratoire';

    protected static ?int $navigationSort = 3;

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
        return LaboratoryTestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LaboratoryTestsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ReferenceRangesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLaboratoryTests::route('/'),
            'create' => CreateLaboratoryTest::route('/create'),
            'view' => ViewLaboratoryTest::route('/{record}'),
            'edit' => EditLaboratoryTest::route('/{record}/edit'),
        ];
    }
}
