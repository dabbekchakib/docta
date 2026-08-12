<?php

namespace App\Filament\Resources\JournalEntries\Schemas;

use App\Enums\JournalEntryStatus;
use App\Enums\JournalEntryType;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class JournalEntryView
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Écriture comptable')
                    ->schema([
                        TextEntry::make('entry_number')
                            ->label('N° écriture'),
                        TextEntry::make('entry_date')
                            ->label('Date')
                            ->date('d/m/Y'),
                        TextEntry::make('type')
                            ->label('Type')
                            ->badge()
                            ->color(fn (JournalEntryType $state): string => $state->getColor()),
                        TextEntry::make('status')
                            ->label('Statut')
                            ->badge()
                            ->color(fn (JournalEntryStatus $state): string => $state->getColor()),
                        TextEntry::make('createdBy.name')
                            ->label('Créée par')
                            ->placeholder('—'),
                        TextEntry::make('posted_at')
                            ->label('Saisie le')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('—'),
                        TextEntry::make('description')
                            ->label('Libellé')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ])
                    ->columns(3),
                Section::make('Lignes de l\'écriture')
                    ->columnSpanFull()
                    ->schema([
                        RepeatableEntry::make('lines')
                            ->label('Lignes')
                            ->schema([
                                TextEntry::make('account')
                                    ->label('Compte')
                                    ->state(fn (TextEntry $component): string => self::accountLabel($component))
                                    ->weight('semibold'),
                                TextEntry::make('debit')
                                    ->label('Débit')
                                    ->formatStateUsing(fn ($state): string => self::money($state))
                                    ->alignEnd(),
                                TextEntry::make('credit')
                                    ->label('Crédit')
                                    ->formatStateUsing(fn ($state): string => self::money($state))
                                    ->alignEnd(),
                                TextEntry::make('notes')
                                    ->label('Notes')
                                    ->placeholder('—'),
                            ])
                            ->columns(4),
                        Section::make('Totaux')
                            ->columns(2)
                            ->schema([
                                TextEntry::make('total_debit')
                                    ->label('Total débit')
                                    ->state(fn (TextEntry $component): string => self::total($component, 'debit'))
                                    ->weight('semibold')
                                    ->color('primary')
                                    ->alignEnd(),
                                TextEntry::make('total_credit')
                                    ->label('Total crédit')
                                    ->state(fn (TextEntry $component): string => self::total($component, 'credit'))
                                    ->weight('semibold')
                                    ->color('primary')
                                    ->alignEnd(),
                            ]),
                    ]),
            ]);
    }

    private static function accountLabel(TextEntry $component): string
    {
        $line = $component->getRecord();

        if (! $line instanceof JournalEntryLine) {
            return '—';
        }

        return $line->account?->label() ?? '—';
    }

    private static function money(mixed $state): string
    {
        if ($state === null || (float) $state <= 0) {
            return '—';
        }

        return number_format((float) $state, 3, ',', ' ').' DT';
    }

    private static function total(TextEntry $component, string $side): string
    {
        $entry = $component->getRecord();

        if (! $entry instanceof JournalEntry) {
            return '—';
        }

        return number_format((float) $entry->lines()->sum($side), 3, ',', ' ').' DT';
    }
}
