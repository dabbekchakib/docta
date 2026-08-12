<?php

namespace App\Filament\Resources\Laboratories\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class LaboratoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Laboratoire')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->color('primary'),
                TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->badge()
                    ->color('gray'),
                TextColumn::make('city')
                    ->label('Ville')
                    ->searchable()
                    ->placeholder('—'),
                TextColumn::make('phone')
                    ->label('Téléphone')
                    ->placeholder('—'),
                TextColumn::make('contact_name')
                    ->label('Contact')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('requests_count')
                    ->label('Demandes')
                    ->counts('requests')
                    ->sortable()
                    ->toggleable(),
                ToggleColumn::make('is_active')
                    ->label('Actif'),
                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('is_active')
                    ->label('Statut')
                    ->options([
                        '1' => 'Actif',
                        '0' => 'Inactif',
                    ]),
            ]);
    }
}
