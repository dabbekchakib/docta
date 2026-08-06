<?php

namespace App\Filament\Resources\Doctors\Tables;

use App\Enums\DoctorStatus;
use App\Enums\Governorate;
use App\Enums\MedicalSpecialty;
use App\Filament\Resources\Doctors\Tables\Actions\ToggleDoctorStatusAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DoctorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('photo')
                    ->label('Photo')
                    ->collection('photo')
                    ->circular()
                    ->width(40)
                    ->height(40),
                TextColumn::make('doctor_code')
                    ->label('Code médecin')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->color('primary'),
                TextColumn::make('full_name')
                    ->label('Nom complet')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(['last_name']),
                TextColumn::make('speciality')
                    ->label('Spécialité')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->label('Téléphone')
                    ->placeholder('—')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('consultation_fee')
                    ->label('Honoraires')
                    ->money('TND')
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Inscrit le')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('speciality')
                    ->label('Spécialité')
                    ->options(MedicalSpecialty::options())
                    ->searchable(),
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(DoctorStatus::options()),
                SelectFilter::make('governorate')
                    ->label('Gouvernorat')
                    ->options(Governorate::options())
                    ->searchable(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                ToggleDoctorStatusAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
