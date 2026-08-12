<?php

namespace App\Filament\Resources\Services\Tables;

use App\Enums\ServiceCategory;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ServicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->color('primary'),
                TextColumn::make('name')
                    ->label('Prestation')
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                TextColumn::make('category')
                    ->label('Catégorie')
                    ->badge()
                    ->sortable(),
                TextColumn::make('price')
                    ->label('Prix')
                    ->formatStateUsing(fn ($record): string => number_format((float) $record->price, 3, ',', ' ').' DT')
                    ->sortable()
                    ->alignEnd(),
                TextColumn::make('taxRate.rate')
                    ->label('TVA')
                    ->formatStateUsing(fn ($state): string => $state !== null ? number_format((float) $state, 2, ',', ' ').' %' : '0,00 %'),
                ToggleColumn::make('is_active')
                    ->label('Active')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Catégorie')
                    ->options(ServiceCategory::options()),
                TernaryFilter::make('is_active')
                    ->label('Activité')
                    ->trueLabel('Active')
                    ->falseLabel('Inactive'),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Modifier'),
                DeleteAction::make()
                    ->label('Supprimer')
                    ->iconButton(),
            ])
            ->defaultSort('code');
    }
}
