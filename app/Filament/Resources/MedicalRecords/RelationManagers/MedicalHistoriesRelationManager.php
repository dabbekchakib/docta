<?php

namespace App\Filament\Resources\MedicalRecords\RelationManagers;

use App\Enums\MedicalHistoryStatus;
use App\Enums\MedicalHistoryType;
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

class MedicalHistoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'medicalHistories';

    protected static ?string $modelLabel = 'antécédent';

    protected static ?string $pluralModelLabel = 'antécédents';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('title')->label('Titre')->required()->maxLength(191),
                Select::make('type')->label('Type')
                    ->options(MedicalHistoryType::options())
                    ->default(MedicalHistoryType::Disease->value),
                Select::make('status')->label('Statut')
                    ->options(MedicalHistoryStatus::options())
                    ->default(MedicalHistoryStatus::Active->value),
                DatePicker::make('diagnosed_at')->label('Diagnostiqué le'),
                DatePicker::make('resolved_at')->label('Résolu le'),
                Textarea::make('description')->label('Description')->rows(3),
                Textarea::make('notes')->label('Notes')->rows(2),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Titre')->searchable()->weight('semibold'),
                TextColumn::make('type')->label('Type')->badge(),
                TextColumn::make('status')->label('Statut')
                    ->badge()
                    ->color(fn (MedicalHistoryStatus $state): string => $state->getColor()),
                TextColumn::make('diagnosed_at')->label('Diagnostiqué le')->date('d/m/Y')->placeholder('—'),
                TextColumn::make('resolved_at')->label('Résolu le')->date('d/m/Y')->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('type')->label('Type')->options(MedicalHistoryType::options()),
                SelectFilter::make('status')->label('Statut')->options(MedicalHistoryStatus::options()),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $data;
    }
}
