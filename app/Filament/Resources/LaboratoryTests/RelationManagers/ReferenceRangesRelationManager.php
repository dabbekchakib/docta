<?php

namespace App\Filament\Resources\LaboratoryTests\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReferenceRangesRelationManager extends RelationManager
{
    protected static string $relationship = 'referenceRanges';

    protected static ?string $title = 'Intervalles de référence';

    protected static ?string $modelLabel = 'intervalle de référence';

    protected static ?string $pluralModelLabel = 'intervalles de référence';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('gender')
                    ->label('Sexe')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'male' => 'Homme',
                        'female' => 'Femme',
                        default => 'Tous',
                    }),
                TextColumn::make('age_min')
                    ->label('Âge min')
                    ->placeholder('—'),
                TextColumn::make('age_max')
                    ->label('Âge max')
                    ->placeholder('—'),
                TextColumn::make('min_value')
                    ->label('Valeur min')
                    ->placeholder('—'),
                TextColumn::make('max_value')
                    ->label('Valeur max')
                    ->placeholder('—'),
                TextColumn::make('unit')
                    ->label('Unité')
                    ->placeholder('—'),
                TextColumn::make('reference_text')
                    ->label('Référence textuelle')
                    ->limit(40)
                    ->placeholder('—'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Ajouter un intervalle')
                    ->form([
                        \Filament\Forms\Components\Select::make('gender')
                            ->label('Sexe')
                            ->options([
                                'all' => 'Tous',
                                'male' => 'Homme',
                                'female' => 'Femme',
                            ])
                            ->default('all')
                            ->required(),
                        \Filament\Forms\Components\TextInput::make('age_min')
                            ->label('Âge minimum (années)')
                            ->numeric()
                            ->minValue(0),
                        \Filament\Forms\Components\TextInput::make('age_max')
                            ->label('Âge maximum (années)')
                            ->numeric()
                            ->minValue(0),
                        \Filament\Forms\Components\TextInput::make('min_value')
                            ->label('Valeur minimale')
                            ->numeric()
                            ->step('0.001'),
                        \Filament\Forms\Components\TextInput::make('max_value')
                            ->label('Valeur maximale')
                            ->numeric()
                            ->step('0.001'),
                        \Filament\Forms\Components\TextInput::make('unit')
                            ->label('Unité')
                            ->maxLength(50),
                        \Filament\Forms\Components\TextInput::make('reference_text')
                            ->label('Référence textuelle')
                            ->maxLength(255)
                            ->helperText('Alternative textuelle lorsque l\'intervalle ne s\'exprime pas en valeurs numériques.'),
                    ])
                    ->modalHeading('Ajouter un intervalle de référence'),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Modifier')
                    ->form([
                        \Filament\Forms\Components\Select::make('gender')
                            ->label('Sexe')
                            ->options([
                                'all' => 'Tous',
                                'male' => 'Homme',
                                'female' => 'Femme',
                            ])
                            ->required(),
                        \Filament\Forms\Components\TextInput::make('age_min')
                            ->label('Âge minimum (années)')
                            ->numeric()
                            ->minValue(0),
                        \Filament\Forms\Components\TextInput::make('age_max')
                            ->label('Âge maximum (années)')
                            ->numeric()
                            ->minValue(0),
                        \Filament\Forms\Components\TextInput::make('min_value')
                            ->label('Valeur minimale')
                            ->numeric()
                            ->step('0.001'),
                        \Filament\Forms\Components\TextInput::make('max_value')
                            ->label('Valeur maximale')
                            ->numeric()
                            ->step('0.001'),
                        \Filament\Forms\Components\TextInput::make('unit')
                            ->label('Unité')
                            ->maxLength(50),
                        \Filament\Forms\Components\TextInput::make('reference_text')
                            ->label('Référence textuelle')
                            ->maxLength(255),
                    ])
                    ->modalHeading('Modifier l\'intervalle de référence'),
                DeleteAction::make()
                    ->label('Supprimer'),
            ]);
    }
}
