<?php

namespace App\Filament\Resources\AccountingAccounts\Schemas;

use App\Enums\AccountingAccountType;
use App\Models\AccountingAccount;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AccountingAccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Compte comptable')
                    ->description('Plan comptable simplifié (norme SCF tunisienne).')
                    ->schema([
                        TextInput::make('code')
                            ->label('Code')
                            ->required()
                            ->maxLength(20)
                            ->unique(ignoreRecord: true)
                            ->placeholder('ex. 7070')
                            ->disabled(fn (string $operation, ?AccountingAccount $record): bool => $operation === 'edit' && (bool) $record?->is_system)
                            ->helperText('Le code d\'un compte système ne peut pas être modifié.'),
                        TextInput::make('name')
                            ->label('Intitulé')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('ex. Prestations de services'),
                        Select::make('type')
                            ->label('Nature')
                            ->options(AccountingAccountType::options())
                            ->required()
                            ->native(false)
                            ->disabled(fn (string $operation, ?AccountingAccount $record): bool => $operation === 'edit' && (bool) $record?->is_system)
                            ->live()
                            ->afterStateUpdated(function (string $operation, $set, $state): void {
                                if ($operation === 'edit') {
                                    return;
                                }

                                $set('normal_balance', AccountingAccountType::from((string) $state)->normalBalance());
                            }),
                        Select::make('category')
                            ->label('Catégorie')
                            ->options([
                                'ressources' => 'Ressources',
                                'immobilisations' => 'Immobilisations',
                                'stocks' => 'Stocks',
                                'tiers' => 'Tiers',
                                'financier' => 'Comptes financiers',
                                'charges' => 'Charges',
                                'produits' => 'Produits',
                            ])
                            ->searchable()
                            ->native(false),
                        Select::make('normal_balance')
                            ->label('Solde normal')
                            ->options([
                                'debit' => 'Débiteur',
                                'credit' => 'Créditeur',
                            ])
                            ->required()
                            ->native(false),
                        Toggle::make('is_active')
                            ->label('Compte actif')
                            ->default(true)
                            ->disabled(fn (string $operation, ?AccountingAccount $record): bool => $operation === 'edit' && (bool) $record?->is_system),
                        Toggle::make('is_system')
                            ->label('Compte système')
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('Marqué système : protégé contre la modification et la suppression.'),
                    ])
                    ->columns(2),
            ]);
    }
}
