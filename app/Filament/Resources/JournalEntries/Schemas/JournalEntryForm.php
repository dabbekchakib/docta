<?php

namespace App\Filament\Resources\JournalEntries\Schemas;

use App\Models\AccountingAccount;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class JournalEntryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Écriture comptable')
                    ->description('Saisie manuelle en partie double (norme SCF). Le total des débits doit être égal au total des crédits.')
                    ->schema([
                        TextInput::make('entry_number')
                            ->label('N° écriture')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Généré automatiquement (ECR-2026-000001)'),
                        DatePicker::make('entry_date')
                            ->label('Date d\'écriture')
                            ->displayFormat('d/m/Y')
                            ->native(false)
                            ->default(now()->toDateString())
                            ->required(),
                        Textarea::make('description')
                            ->label('Libellé')
                            ->rows(2)
                            ->maxLength(255),
                    ])
                    ->columns(3),
                Section::make('Lignes de l\'écriture')
                    ->description('Chaque ligne indique un compte comptable et un montant au débit ou au crédit.')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('lines')
                            ->label('Lignes')
                            ->defaultItems(2)
                            ->minItems(2)
                            ->collapsible()
                            ->reorderableWithButtons()
                            ->addActionLabel('Ajouter une ligne')
                            ->schema([
                                Select::make('accounting_account_id')
                                    ->label('Compte')
                                    ->options(fn (): array => self::accountOptions())
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->native(false)
                                    ->columnSpan(2),
                                TextInput::make('debit')
                                    ->label('Débit')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step('0.001')
                                    ->prefix('DT')
                                    ->placeholder('0,000'),
                                TextInput::make('credit')
                                    ->label('Crédit')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step('0.001')
                                    ->prefix('DT')
                                    ->placeholder('0,000'),
                                TextInput::make('notes')
                                    ->label('Notes')
                                    ->maxLength(255)
                                    ->columnSpan(2),
                            ])
                            ->columns(4),
                    ]),
            ]);
    }

    /**
     * @return array<int, string>
     */
    private static function accountOptions(): array
    {
        return AccountingAccount::query()
            ->where('is_active', true)
            ->orderBy('code')
            ->get()
            ->mapWithKeys(fn (AccountingAccount $account): array => [$account->id => $account->label()])
            ->all();
    }
}
