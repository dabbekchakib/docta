<?php

namespace App\Filament\Resources\Permissions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PermissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nom technique')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->helperText('Exemple : patients.view')
                    ->autofocus(),
                Select::make('guard_name')
                    ->label('Guard')
                    ->options(['web' => 'web'])
                    ->default('web')
                    ->required(),
            ]);
    }
}
