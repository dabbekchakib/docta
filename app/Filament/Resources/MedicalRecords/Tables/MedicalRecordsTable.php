<?php

namespace App\Filament\Resources\MedicalRecords\Tables;

use App\Enums\BloodGroup;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class MedicalRecordsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('medical_record_number')
                    ->label('N° DMP')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->color('primary'),
                TextColumn::make('patient.full_name')
                    ->label('Patient')
                    ->searchable(['first_name', 'last_name', 'patient_number']),
                TextColumn::make('patient.patient_number')
                    ->label('N° patient')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('full_blood_group')
                    ->label('Groupe sanguin')
                    ->badge()
                    ->color('danger'),
                TextColumn::make('critical_allergies_count')
                    ->label('Allergies critiques')
                    ->badge()
                    ->color(fn (mixed $state): string => ($state ?? 0) > 0 ? 'danger' : 'gray')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->iconColor('danger'),
                TextColumn::make('active_chronic_diseases_count')
                    ->label('Maladies actives')
                    ->badge()
                    ->color(fn (mixed $state): string => ($state ?? 0) > 0 ? 'warning' : 'gray'),
                TextColumn::make('active_medications_count')
                    ->label('Traitements actifs')
                    ->badge()
                    ->color(fn (mixed $state): string => ($state ?? 0) > 0 ? 'info' : 'gray')
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->label('Mis à jour le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('blood_group')
                    ->label('Groupe sanguin')
                    ->options(BloodGroup::options()),
                TernaryFilter::make('critical_allergies')
                    ->label('Allergies critiques')
                    ->queries(
                        true: fn ($query) => $query->whereHas('allergies', fn ($q) => $q->where('status', 'active')->whereIn('severity', ['severe', 'critical'])),
                        false: fn ($query) => $query->whereDoesntHave('allergies', fn ($q) => $q->where('status', 'active')->whereIn('severity', ['severe', 'critical'])),
                        blank: fn ($query) => $query,
                    ),
                TernaryFilter::make('active_chronic_diseases')
                    ->label('Maladies chroniques actives')
                    ->queries(
                        true: fn ($query) => $query->whereHas('chronicDiseases', fn ($q) => $q->whereIn('status', ['active', 'controlled'])),
                        false: fn ($query) => $query->whereDoesntHave('chronicDiseases', fn ($q) => $q->whereIn('status', ['active', 'controlled'])),
                        blank: fn ($query) => $query,
                    ),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->defaultSort('updated_at', 'desc');
    }
}
