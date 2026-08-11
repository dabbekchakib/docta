<?php

namespace App\Filament\Resources\Prescriptions\Tables;

use App\Enums\PrescriptionStatus;
use App\Filament\Resources\Prescriptions\Actions\CancelPrescriptionAction;
use App\Filament\Resources\Prescriptions\Actions\DuplicatePrescriptionAction;
use App\Filament\Resources\Prescriptions\Actions\IssuePrescriptionAction;
use App\Filament\Resources\Prescriptions\Actions\PrintPrescriptionAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PrescriptionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('prescription_number')
                    ->label('N° ordonnance')
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
                TextColumn::make('prescription_date')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable()
                    ->searchable(),
              
                TextColumn::make('valid_until')
                    ->label('Valable jusqu\'au')
                    ->date('d/m/Y')
                    ->placeholder('—')
                    ->toggleable(),
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
                    ->options(PrescriptionStatus::options()),
                SelectFilter::make('patient_id')
                    ->label('Patient')
                    ->relationship('patient', 'full_name')
                    ->searchable(),
                SelectFilter::make('doctor_id')
                    ->label('Médecin')
                    ->relationship('doctor', 'full_name')
                    ->searchable(),
            ])
            ->recordActions([
                ViewAction::make(),
                IssuePrescriptionAction::make(),
                CancelPrescriptionAction::make(),
                PrintPrescriptionAction::make(),
                DuplicatePrescriptionAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
