<?php

namespace App\Filament\Resources\MedicalRecords\RelationManagers;

use App\Enums\MedicalHistoryStatus;
use App\Enums\RelativeType;
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

class FamilyHistoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'familyHistories';

    protected static ?string $modelLabel = 'antécédent familial';

    protected static ?string $pluralModelLabel = 'antécédents familiaux';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('condition')->label('Affection')->required()->maxLength(191),
                Select::make('relative')->label('Lien de parenté')
                    ->options(RelativeType::options())
                    ->default(RelativeType::Other->value),
                Select::make('status')->label('Statut')
                    ->options(MedicalHistoryStatus::options())
                    ->default(MedicalHistoryStatus::Unknown->value),
                DatePicker::make('diagnosed_at')->label('Diagnostiquée le'),
                Textarea::make('description')->label('Description')->rows(3),
                Textarea::make('notes')->label('Notes')->rows(2),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('condition')->label('Affection')->searchable()->weight('semibold'),
                TextColumn::make('relative')->label('Lien de parenté')->badge(),
                TextColumn::make('status')->label('Statut')
                    ->badge()
                    ->color(fn (MedicalHistoryStatus $state): string => $state->getColor()),
                TextColumn::make('diagnosed_at')->label('Diagnostiquée le')->date('d/m/Y')->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('relative')->label('Lien de parenté')->options(RelativeType::options()),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
