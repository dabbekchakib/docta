<?php

namespace App\Filament\Resources\LaboratoryTests\Tables;

use App\Enums\SampleType;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LaboratoryTestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Examen')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->color('primary'),
                TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->badge()
                    ->color('gray'),
                TextColumn::make('category.name')
                    ->label('Catégorie')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sample_type')
                    ->label('Prélèvement')
                    ->badge()
                    ->formatStateUsing(fn (SampleType $state): string => $state->getLabel())
                    ->color(fn (SampleType $state): string => $state->getColor()),
                TextColumn::make('unit')
                    ->label('Unité')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('requires_fasting')
                    ->label('Jeûne')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Oui' : 'Non')
                    ->color(fn (bool $state): string => $state ? 'warning' : 'gray')
                    ->toggleable(),
                ToggleColumn::make('is_active')
                    ->label('Actif'),
            ])
            ->filters([
                SelectFilter::make('test_category_id')
                    ->label('Catégorie')
                    ->relationship('category', 'name')
                    ->searchable(),
                SelectFilter::make('sample_type')
                    ->label('Type de prélèvement')
                    ->options(SampleType::options()),
                SelectFilter::make('is_active')
                    ->label('Statut')
                    ->options([
                        '1' => 'Actif',
                        '0' => 'Inactif',
                    ]),
            ]);
    }
}
