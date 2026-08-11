<?php

namespace App\Filament\Resources\MedicalRecords\RelationManagers;

use App\Enums\AllergySeverity;
use App\Enums\ChronicDiseaseStatus;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ChronicDiseasesRelationManager extends RelationManager
{
    protected static string $relationship = 'chronicDiseases';

    protected static ?string $modelLabel = 'maladie chronique';

    protected static ?string $pluralModelLabel = 'maladies chroniques';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('disease_name')->label('Maladie')->required()->maxLength(191),
                TextInput::make('icd_code')->label('Code CIM-10')->maxLength(10),
                Select::make('status')->label('Statut')
                    ->options(ChronicDiseaseStatus::options())
                    ->default(ChronicDiseaseStatus::Active->value),
                Select::make('severity')->label('Sévérité')
                    ->options(AllergySeverity::options())
                    ->default(AllergySeverity::Moderate->value),
                DatePicker::make('diagnosed_at')->label('Diagnostiquée le'),
                TextInput::make('treatment')->label('Traitement')->maxLength(191),
                Textarea::make('notes')->label('Notes')->rows(2),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('disease_name')->label('Maladie')->searchable()->weight('semibold'),
                TextColumn::make('icd_code')->label('CIM-10')->badge()->color('gray')->placeholder('—'),
                TextColumn::make('status')->label('Statut')
                    ->badge()
                    ->color(fn (ChronicDiseaseStatus $state): string => $state->getColor()),
                TextColumn::make('severity')->label('Sévérité')
                    ->badge()
                    ->color(fn (AllergySeverity $state): string => $state->getColor()),
                TextColumn::make('treatment')->label('Traitement')->limit(40)->placeholder('—'),
                TextColumn::make('diagnosed_at')->label('Diagnostiquée le')->date('d/m/Y')->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('status')->label('Statut')->options(ChronicDiseaseStatus::options()),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
