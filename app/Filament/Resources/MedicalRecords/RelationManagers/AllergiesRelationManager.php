<?php

namespace App\Filament\Resources\MedicalRecords\RelationManagers;

use App\Enums\AllergySeverity;
use App\Enums\AllergyStatus;
use App\Enums\AllergyType;
use Filament\Actions\CreateAction;
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
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class AllergiesRelationManager extends RelationManager
{
    protected static string $relationship = 'allergies';

    protected static ?string $modelLabel = 'allergie';

    protected static ?string $pluralModelLabel = 'allergies';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('allergen')->label('Allergène')->required()->maxLength(191),
                Select::make('type')->label('Type')
                    ->options(AllergyType::options())
                    ->default(AllergyType::Medication->value),
                Select::make('severity')->label('Sévérité')
                    ->options(AllergySeverity::options())
                    ->default(AllergySeverity::Moderate->value)
                    ->required(),
                Select::make('status')->label('Statut')
                    ->options(AllergyStatus::options())
                    ->default(AllergyStatus::Active->value),
                DatePicker::make('discovered_at')->label('Découverte'),
                TextInput::make('reaction')->label('Réaction')->maxLength(191),
                Textarea::make('notes')->label('Notes')->rows(2),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('allergen')->label('Allergène')->searchable()->weight('semibold'),
                TextColumn::make('type')->label('Type')->badge(),
                TextColumn::make('severity')->label('Sévérité')
                    ->badge()
                    ->color(fn (AllergySeverity $state): string => $state->getColor()),
                TextColumn::make('reaction')->label('Réaction')->limit(40)->placeholder('—'),
                TextColumn::make('status')->label('Statut')
                    ->badge()
                    ->color(fn (AllergyStatus $state): string => $state->getColor()),
                TextColumn::make('discovered_at')->label('Découverte')->date('d/m/Y')->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('severity')->label('Sévérité')->options(AllergySeverity::options()),
                SelectFilter::make('status')->label('Statut')->options(AllergyStatus::options()),
                TernaryFilter::make('critical')
                    ->label('Critique')
                    ->queries(
                        true: fn ($query) => $query->where('status', AllergyStatus::Active->value)->whereIn('severity', ['severe', 'critical']),
                        false: fn ($query) => $query->where(function ($q) {
                            $q->where('status', '!=', AllergyStatus::Active->value)->orWhereNotIn('severity', ['severe', 'critical']);
                        }),
                        blank: fn ($query) => $query,
                    ),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
