<?php

namespace App\Filament\Resources\Roles\Schemas;

use App\Enums\Permission as PermissionEnum;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Permission;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informations')
                    ->description('Détails du rôle')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nom technique')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Exemple : secretary, doctor…')
                            ->autofocus(),
                        Select::make('guard_name')
                            ->label('Guard')
                            ->options(['web' => 'web'])
                            ->default('web')
                            ->required(),
                    ])
                    ->columns(2),
                Section::make('Permissions')
                    ->description('Permissions accordées à ce rôle')
                    ->schema([
                        CheckboxList::make('permissions')
                            ->label('Permissions')
                            ->relationship('permissions')
                            ->getOptionLabelFromRecordUsing(
                                fn (Permission $permission): string => PermissionEnum::tryFrom($permission->name)?->label() ?? $permission->name
                            )
                            ->columns(2),
                    ]),
            ]);
    }
}
