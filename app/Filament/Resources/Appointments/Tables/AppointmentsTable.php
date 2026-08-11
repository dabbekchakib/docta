<?php

namespace App\Filament\Resources\Appointments\Tables;

use App\Enums\AppointmentStatus;
use App\Enums\AppointmentType;
use App\Filament\Resources\Consultations\Actions\StartConsultationAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AppointmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('appointment_number')
                    ->label('N° RDV')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->color('primary'),
                TextColumn::make('patient.full_name')
                    ->label('Patient')
                    ->searchable(['first_name', 'last_name', 'patient_number']),
                TextColumn::make('doctor.full_name')
                    ->label('Médecin')
                    ->searchable(['first_name', 'last_name']),
                TextColumn::make('appointment_date')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('start_time')
                    ->label('Début')
                    ->sortable(),
                TextColumn::make('end_time')
                    ->label('Fin')
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (AppointmentType $state): string => $state->getColor()),
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
                    ->options(AppointmentStatus::options()),
                SelectFilter::make('type')
                    ->label('Type')
                    ->options(AppointmentType::options()),
                SelectFilter::make('doctor_id')
                    ->label('Médecin')
                    ->relationship('doctor', 'full_name')
                    ->searchable(),
            ])
            ->recordActions([
                ViewAction::make(),
                StartConsultationAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
