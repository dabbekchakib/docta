<?php

namespace App\Filament\Resources\Laboratories\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LaboratoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Identité du laboratoire')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')
                            ->label('Nom du laboratoire')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('code')
                            ->label('Code')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50)
                            ->helperText('Identifiant court et unique du laboratoire.'),
                        TextInput::make('contact_name')
                            ->label('Personne de contact')
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label('Téléphone')
                            ->tel()
                            ->maxLength(50),
                        TextInput::make('email')
                            ->label('Adresse email')
                            ->email()
                            ->maxLength(255),
                    ])
                    ->columns(2),
                Section::make('Coordonnées')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('address')
                            ->label('Adresse')
                            ->maxLength(255),
                        TextInput::make('city')
                            ->label('Ville')
                            ->maxLength(100),
                    ])
                    ->columns(2),
                Section::make('Statut et remarques')
                    ->columnSpanFull()
                    ->schema([
                        \Filament\Forms\Components\Toggle::make('is_active')
                            ->label('Laboratoire actif')
                            ->default(true),
                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(1),
            ]);
    }
}
