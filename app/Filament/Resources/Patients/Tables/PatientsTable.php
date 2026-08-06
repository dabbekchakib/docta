<?php

namespace App\Filament\Resources\Patients\Tables;

use App\Enums\Governorate;
use App\Enums\PatientGender;
use App\Enums\PatientStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PatientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('photo')
                    ->label('Photo')
                    ->circular()
                    ->disk('public')
                    ->width(40)
                    ->height(40),
                TextColumn::make('patient_number')
                    ->label('N° dossier')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->color('primary'),
                TextColumn::make('full_name')
                    ->label('Nom complet')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(['last_name']),
                TextColumn::make('gender')
                    ->label('Sexe')
                    ->sortable(),
                TextColumn::make('phone')
                    ->label('Téléphone')
                    ->searchable(),
                TextColumn::make('cnam_number')
                    ->label('CNAM')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('age')
                    ->label('Âge')
                    ->placeholder('—')
                    ->formatStateUsing(fn (mixed $state): string => $state !== null ? $state.' ans' : '—'),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(PatientStatus::options()),
                SelectFilter::make('gender')
                    ->label('Sexe')
                    ->options(PatientGender::options()),
                SelectFilter::make('governorate')
                    ->label('Gouvernorat')
                    ->options(Governorate::options())
                    ->searchable(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
