<?php

namespace App\Filament\Resources\AccountingAccounts\Tables;

use App\Enums\AccountingAccountType;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class AccountingAccountsTable
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
                    ->label('Intitulé')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Nature')
                    ->badge()
                    ->sortable(),
                TextColumn::make('category')
                    ->label('Catégorie')
                    ->toggleable()
                    ->sortable(),
                TextColumn::make('normal_balance')
                    ->label('Solde normal')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'debit' ? 'Débiteur' : 'Créditeur')
                    ->color(fn (string $state): string => $state === 'debit' ? 'primary' : 'warning')
                    ->toggleable(),
                TextColumn::make('is_system')
                    ->label('Système')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Oui' : 'Non')
                    ->color(fn (bool $state): string => $state ? 'gray' : 'success')
                    ->toggleable(),
                TextColumn::make('is_active')
                    ->label('Actif')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Actif' : 'Inactif')
                    ->color(fn (bool $state): string => $state ? 'success' : 'danger')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Nature')
                    ->options(AccountingAccountType::options()),
                SelectFilter::make('category')
                    ->label('Catégorie')
                    ->options([
                        'ressources' => 'Ressources',
                        'immobilisations' => 'Immobilisations',
                        'stocks' => 'Stocks',
                        'tiers' => 'Tiers',
                        'financier' => 'Comptes financiers',
                        'charges' => 'Charges',
                        'produits' => 'Produits',
                    ]),
                TernaryFilter::make('is_system')
                    ->label('Compte système'),
                TernaryFilter::make('is_active')
                    ->label('Activité')
                    ->trueLabel('Actif')
                    ->falseLabel('Inactif'),
            ])
            ->defaultSort('code');
    }
}
