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
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class VaccinationsRelationManager extends RelationManager
{
    protected static string $relationship = 'vaccinations';

    protected static ?string $modelLabel = 'vaccination';

    protected static ?string $pluralModelLabel = 'vaccinations';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('vaccine_name')->label('Vaccin')->required()->maxLength(191),
                TextInput::make('dose_number')->label('Dose')->numeric()->minValue(1),
                DatePicker::make('administered_at')->label('Administré le'),
                DatePicker::make('next_due_at')->label('Prochaine dose'),
                TextInput::make('provider')->label('Prestataire')->maxLength(191),
                TextInput::make('batch_number')->label('N° de lot')->maxLength(191),
                Textarea::make('notes')->label('Notes')->rows(2),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('vaccine_name')->label('Vaccin')->searchable()->weight('semibold'),
                TextColumn::make('dose_number')->label('Dose')->badge()->color('primary')->placeholder('—'),
                TextColumn::make('administered_at')->label('Administré le')->date('d/m/Y')->placeholder('—'),
                TextColumn::make('next_due_at')->label('Prochaine dose')
                    ->date('d/m/Y')
                    ->color(fn (?string $state): ?string => $state && $state <= now()->toDateString() ? 'danger' : null)
                    ->placeholder('—'),
                TextColumn::make('batch_number')->label('N° de lot')->placeholder('—'),
            ])
            ->filters([
                Filter::make('due')
                    ->label('Dose à venir')
                    ->query(fn (Builder $query) => $query->whereNotNull('next_due_at')->whereDate('next_due_at', '<=', now())),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('administered_at', 'desc');
    }
}
