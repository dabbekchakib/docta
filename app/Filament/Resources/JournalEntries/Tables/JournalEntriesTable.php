<?php

namespace App\Filament\Resources\JournalEntries\Tables;

use App\Enums\JournalEntryStatus;
use App\Enums\JournalEntryType;
use App\Filament\Resources\JournalEntries\Actions\CancelJournalEntryAction;
use App\Filament\Resources\JournalEntries\Actions\PostJournalEntryAction;
use App\Models\JournalEntry;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class JournalEntriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('entry_number')
                    ->label('N° écriture')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->color('primary'),
                TextColumn::make('entry_date')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->sortable(),
                TextColumn::make('description')
                    ->label('Libellé')
                    ->limit(40)
                    ->toggleable(),
                TextColumn::make('lines_count')
                    ->label('Lignes')
                    ->state(fn (JournalEntry $record): int => $record->lines()->count())
                    ->alignCenter()
                    ->sortable(),
                TextColumn::make('debit_total')
                    ->label('Débit')
                    ->state(fn (JournalEntry $record): string => number_format((float) $record->lines()->sum('debit'), 3, ',', ' '))
                    ->alignEnd()
                    ->sortable(),
                TextColumn::make('credit_total')
                    ->label('Crédit')
                    ->state(fn (JournalEntry $record): string => number_format((float) $record->lines()->sum('credit'), 3, ',', ' '))
                    ->alignEnd()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(JournalEntryStatus::options()),
                SelectFilter::make('type')
                    ->label('Type')
                    ->options(JournalEntryType::options()),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->label('Modifier')
                    ->visible(fn (JournalEntry $record): bool => $record->status === JournalEntryStatus::Draft
                        && (auth()->user()?->can('update', $record) ?? false)),
                PostJournalEntryAction::make(),
                CancelJournalEntryAction::make(),
                DeleteAction::make()
                    ->visible(fn (JournalEntry $record): bool => $record->status === JournalEntryStatus::Draft
                        && (auth()->user()?->can('delete', $record) ?? false)),
            ])
            ->defaultSort('entry_date', 'desc');
    }
}
