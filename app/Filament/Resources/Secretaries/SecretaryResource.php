<?php

namespace App\Filament\Resources\Secretaries;

use App\Filament\Resources\Secretaries\Pages\CreateSecretary;
use App\Filament\Resources\Secretaries\Pages\EditSecretary;
use App\Filament\Resources\Secretaries\Pages\ListSecretaries;
use App\Filament\Resources\Secretaries\Pages\ViewSecretary;
use App\Filament\Resources\Secretaries\Schemas\SecretaryForm;
use App\Filament\Resources\Secretaries\Schemas\SecretaryView;
use App\Filament\Resources\Secretaries\Tables\SecretariesTable;
use App\Models\Secretary;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SecretaryResource extends Resource
{
    protected static ?string $model = Secretary::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|\UnitEnum|null $navigationGroup = 'Gestion';

    protected static ?string $navigationLabel = 'Secrétaires';

    protected static ?string $modelLabel = 'secrétaire';

    protected static ?string $pluralModelLabel = 'secrétaires';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'full_name';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyPermission([
            'secretaries.view',
            'secretaries.create',
            'secretaries.update',
            'secretaries.delete',
        ]) ?? false;
    }

    /**
     * @return array<int, string>
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['secretary_code', 'first_name', 'last_name', 'email', 'mobile', 'employee_number'];
    }

    public static function form(Schema $schema): Schema
    {
        return SecretaryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SecretaryView::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SecretariesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSecretaries::route('/'),
            'create' => CreateSecretary::route('/create'),
            'view' => ViewSecretary::route('/{record}'),
            'edit' => EditSecretary::route('/{record}/edit'),
        ];
    }
}
