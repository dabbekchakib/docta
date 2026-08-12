<?php

namespace App\Filament\Resources\TaxRates\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class TaxRatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),
                TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->color('gray'),
                TextColumn::make('rate')
                    ->label('Taux')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 2, ',', ' ').' %')
                    ->sortable()
                    ->alignEnd(),
                ToggleColumn::make('is_active')
                    ->label('Actif')
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Activité')
                    ->trueLabel('Actif')
                    ->falseLabel('Inactif'),
            ])
            ->defaultSort('rate');
    }
}
