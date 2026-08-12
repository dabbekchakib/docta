<?php

namespace App\Filament\Resources\PaymentMethods\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PaymentMethodsTable
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
                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->sortable(),
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
            ->defaultSort('name');
    }
}
