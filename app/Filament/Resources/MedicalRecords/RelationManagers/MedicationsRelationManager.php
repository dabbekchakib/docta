<?php

namespace App\Filament\Resources\MedicalRecords\RelationManagers;

use App\Enums\MedicationStatus;
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

class MedicationsRelationManager extends RelationManager
{
    protected static string $relationship = 'medications';

    protected static ?string $modelLabel = 'traitement';

    protected static ?string $pluralModelLabel = 'traitements';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('name')->label('Médicament')->required()->maxLength(191),
                TextInput::make('active_ingredient')->label('Principe actif')->maxLength(191),
                TextInput::make('dosage')->label('Dosage')->maxLength(191),
                TextInput::make('frequency')->label('Fréquence')->placeholder('Ex : 1 comprimé matin et soir')->maxLength(191),
                Select::make('route')->label('Voie')
                    ->options([
                        'orale' => 'Orale',
                        'intraveineuse' => 'Intraveineuse',
                        'intramusculaire' => 'Intramusculaire',
                        'sous_cutanee' => 'Sous-cutanée',
                        'inhalation' => 'Inhalation',
                        'topique' => 'Topique',
                        'autre' => 'Autre',
                    ]),
                Select::make('status')->label('Statut')
                    ->options(MedicationStatus::options())
                    ->default(MedicationStatus::Active->value),
                DatePicker::make('started_at')->label('Début'),
                DatePicker::make('ended_at')->label('Fin'),
                TextInput::make('prescriber')->label('Prescripteur')->maxLength(191),
                Textarea::make('notes')->label('Notes')->rows(2),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Médicament')->searchable()->weight('semibold'),
                TextColumn::make('dosage')->label('Dosage')->placeholder('—'),
                TextColumn::make('frequency')->label('Fréquence')->limit(30)->placeholder('—'),
                TextColumn::make('status')->label('Statut')
                    ->badge()
                    ->color(fn (MedicationStatus $state): string => $state->getColor()),
                TextColumn::make('started_at')->label('Début')->date('d/m/Y')->placeholder('—'),
                TextColumn::make('ended_at')->label('Fin')->date('d/m/Y')->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('status')->label('Statut')->options(MedicationStatus::options()),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
