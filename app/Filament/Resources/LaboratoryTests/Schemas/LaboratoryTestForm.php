<?php

namespace App\Filament\Resources\LaboratoryTests\Schemas;

use App\Enums\SampleType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LaboratoryTestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Examen de laboratoire')
                    ->columnSpanFull()
                    ->schema([
                        Select::make('test_category_id')
                            ->label('Catégorie')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('name')
                            ->label('Nom de l\'examen')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('code')
                            ->label('Code')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50),
                        Select::make('sample_type')
                            ->label('Type de prélèvement')
                            ->options(SampleType::options())
                            ->default(SampleType::Blood->value)
                            ->native(false)
                            ->required(),
                        TextInput::make('unit')
                            ->label('Unité')
                            ->maxLength(50)
                            ->placeholder('ex. g/L, mmol/L, mg/dL'),
                        TextInput::make('default_reference_value')
                            ->label('Valeurs de référence (défaut)')
                            ->maxLength(255)
                            ->placeholder('ex. 0.5 – 1.1 g/L'),
                    ])
                    ->columns(3),
                Section::make('Préparation du patient')
                    ->columnSpanFull()
                    ->schema([
                        Toggle::make('requires_fasting')
                            ->label('À jeun avant le prélèvement')
                            ->default(false),
                        Toggle::make('is_active')
                            ->label('Examen actif')
                            ->default(true),
                        Textarea::make('instructions')
                            ->label('Instructions')
                            ->rows(3)
                            ->columnSpanFull()
                            ->helperText('Consignes affichées au patient (ex. jeûne, arrêt d\'un traitement).'),
                    ])
                    ->columns(2),
                Section::make('Description')
                    ->columnSpanFull()
                    ->schema([
                        Textarea::make('description')
                            ->label('Description')
                            ->rows(3),
                    ]),
            ]);
    }
}
