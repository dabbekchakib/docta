<?php

namespace App\Filament\Resources\TaxRates\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TaxRateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Taux de taxe')
                    ->description('Les taux sont configurables et jamais codés en dur (TVA 0, 7, 13, 19 %…).')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('ex. TVA 19 %'),
                        TextInput::make('code')
                            ->label('Code')
                            ->required()
                            ->maxLength(50)
                            ->placeholder('ex. TVA19'),
                        TextInput::make('rate')
                            ->label('Taux (%)')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->maxValue(100)
                            ->step('0.01')
                            ->suffix('%'),
                        Toggle::make('is_active')
                            ->label('Taux actif')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }
}
