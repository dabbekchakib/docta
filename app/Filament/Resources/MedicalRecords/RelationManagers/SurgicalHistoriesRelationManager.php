<?php

namespace App\Filament\Resources\MedicalRecords\RelationManagers;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SurgicalHistoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'surgicalHistories';

    protected static ?string $modelLabel = 'intervention chirurgicale';

    protected static ?string $pluralModelLabel = 'interventions chirurgicales';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('procedure_name')->label('Intervention')->required()->maxLength(191),
                TextInput::make('hospital')->label('Établissement')->maxLength(191),
                TextInput::make('surgeon')->label('Chirurgien')->maxLength(191),
                DatePicker::make('performed_at')->label('Réalisée le'),
                TextInput::make('reason')->label('Motif')->maxLength(191),
                TextInput::make('complications')->label('Complications')->maxLength(191),
                Textarea::make('notes')->label('Notes')->rows(2),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('procedure_name')->label('Intervention')->searchable()->weight('semibold'),
                TextColumn::make('hospital')->label('Établissement')->placeholder('—'),
                TextColumn::make('surgeon')->label('Chirurgien')->placeholder('—'),
                TextColumn::make('performed_at')->label('Réalisée le')->date('d/m/Y')->placeholder('—'),
                TextColumn::make('complications')->label('Complications')->limit(30)->placeholder('—'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('performed_at', 'desc');
    }
}
