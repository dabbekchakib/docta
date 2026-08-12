<?php

namespace App\Filament\Resources\Services\Schemas;

use App\Enums\ServiceCategory;
use App\Models\TaxRate;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Prestation')
                    ->description('Tarif facturé au patient, hors taxes de remise.')
                    ->schema([
                        TextInput::make('code')
                            ->label('Code')
                            ->required()
                            ->maxLength(50)
                            ->placeholder('ex. PR-0001'),
                        TextInput::make('name')
                            ->label('Nom de la prestation')
                            ->required()
                            ->maxLength(255),
                        Select::make('category')
                            ->label('Catégorie')
                            ->options(ServiceCategory::options())
                            ->required()
                            ->native(false),
                        TextInput::make('price')
                            ->label('Prix (TND)')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->default(0)
                            ->prefix('DT')
                            ->step('0.001'),
                        Select::make('tax_rate_id')
                            ->label('Taux de taxe appliqué')
                            ->relationship('taxRate', 'name')
                            ->getOptionLabelFromRecordUsing(fn (TaxRate $record): string => $record->name.' ('.$record->rateLabel().')')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->helperText('Laissé vide : exonéré (0 %).'),
                        TextInput::make('description')
                            ->label('Description')
                            ->maxLength(255),
                        Toggle::make('is_active')
                            ->label('Prestation active')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }
}
