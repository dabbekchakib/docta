<?php

namespace App\Filament\Resources\MedicalRecords\RelationManagers;

use App\Enums\AlcoholStatus;
use App\Enums\SmokingStatus;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LifestyleRelationManager extends RelationManager
{
    protected static string $relationship = 'lifestyle';

    protected static ?string $modelLabel = 'mode de vie';

    protected static ?string $pluralModelLabel = 'mode de vie';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('smoking_status')->label('Tabac')
                    ->options(SmokingStatus::options())
                    ->default(SmokingStatus::Never->value),
                TextInput::make('smoking_quantity')->label('Quantité (paquets/an)')->maxLength(191),
                Select::make('alcohol_status')->label('Alcool')
                    ->options(AlcoholStatus::options())
                    ->default(AlcoholStatus::Never->value),
                TextInput::make('physical_activity')->label('Activité physique')->maxLength(191),
                TextInput::make('diet')->label('Alimentation')->maxLength(191),
                Select::make('sleep_quality')->label('Qualité du sommeil')
                    ->options([
                        'bonne' => 'Bonne',
                        'moyenne' => 'Moyenne',
                        'mauvaise' => 'Mauvaise',
                    ]),
                TextInput::make('occupation_risk')->label('Risque professionnel')->maxLength(191),
                Textarea::make('other_risks')->label('Autres risques')->rows(2),
                Textarea::make('notes')->label('Notes')->rows(2)->columnSpanFull(),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('smoking_status')->label('Tabac')->badge(),
                TextColumn::make('alcohol_status')->label('Alcool')->badge(),
                TextColumn::make('physical_activity')->label('Activité physique')->placeholder('—'),
                TextColumn::make('sleep_quality')->label('Sommeil')->placeholder('—'),
                TextColumn::make('occupation_risk')->label('Risque professionnel')->limit(30)->placeholder('—'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
