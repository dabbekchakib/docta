<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Support\Roles;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informations')
                    ->description('Informations personnelles du compte')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nom complet')
                            ->required()
                            ->maxLength(255)
                            ->autofocus(),
                        TextInput::make('email')
                            ->label('Adresse email')
                            ->email()
                            ->unique(ignoreRecord: true)
                            ->required()
                            ->maxLength(255),
                        TextInput::make('password')
                            ->label('Mot de passe')
                            ->password()
                            ->revealable()
                            ->required(fn (Page $livewire): bool => $livewire instanceof CreateRecord)
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->maxLength(255)
                            ->helperText(fn (Page $livewire): ?string => $livewire instanceof CreateRecord ? null : 'Laissez vide pour conserver le mot de passe actuel.'),
                    ])
                    ->columns(2),
                Section::make('Rôles & accès')
                    ->description('Choisissez les rôles attribués à cet utilisateur')
                    ->schema([
                        CheckboxList::make('roles')
                            ->label('Rôles')
                            ->options(fn (): array => Roles::options())
                            ->columns(2)
                            ->required(),
                    ]),
            ]);
    }
}
