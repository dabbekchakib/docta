<?php

namespace App\Filament\Resources\PaymentMethods\Schemas;

use App\Enums\PaymentMethodType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PaymentMethodForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Moyen de paiement')
                    ->description('Espèces, carte, chèque, virement, CNAM, assurance…')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('ex. Espèces'),
                        TextInput::make('code')
                            ->label('Code')
                            ->required()
                            ->maxLength(50)
                            ->placeholder('ex. CASH'),
                        Select::make('type')
                            ->label('Type')
                            ->options(PaymentMethodType::options())
                            ->required()
                            ->native(false),
                        TextInput::make('description')
                            ->label('Description')
                            ->maxLength(255),
                        Toggle::make('is_active')
                            ->label('Moyen actif')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }
}
