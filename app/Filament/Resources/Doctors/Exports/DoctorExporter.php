<?php

namespace App\Filament\Resources\Doctors\Exports;

use App\Enums\DoctorGender;
use App\Enums\DoctorStatus;
use App\Enums\Governorate;
use App\Enums\MedicalSpecialty;
use App\Models\Doctor;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class DoctorExporter extends Exporter
{
    protected static ?string $model = Doctor::class;

    /**
     * @return array<int, ExportColumn>
     */
    public static function getColumns(): array
    {
        return [
            ExportColumn::make('doctor_code')->label('Code médecin'),
            ExportColumn::make('full_name')->label('Nom complet'),
            ExportColumn::make('gender')
                ->label('Sexe')
                ->formatStateUsing(fn (mixed $state): string => $state instanceof DoctorGender ? $state->getLabel() : (string) ($state ?? '')),
            ExportColumn::make('birth_date')
                ->label('Date de naissance')
                ->formatStateUsing(fn (mixed $state): string => $state?->format('d/m/Y') ?? ''),
            ExportColumn::make('speciality')
                ->label('Spécialité')
                ->formatStateUsing(fn (mixed $state): string => $state instanceof MedicalSpecialty ? $state->getLabel() : (string) ($state ?? '')),
            ExportColumn::make('sub_speciality')->label('Sous-spécialité'),
            ExportColumn::make('order_number')->label('N° d\'ordre'),
            ExportColumn::make('national_id')->label('CIN'),
            ExportColumn::make('email')->label('Email'),
            ExportColumn::make('phone')->label('Téléphone'),
            ExportColumn::make('mobile')->label('Mobile'),
            ExportColumn::make('city')->label('Ville'),
            ExportColumn::make('governorate')
                ->label('Gouvernorat')
                ->formatStateUsing(fn (mixed $state): string => $state instanceof Governorate ? $state->getLabel() : (string) ($state ?? '')),
            ExportColumn::make('consultation_fee')
                ->label('Honoraires (DT)'),
            ExportColumn::make('consultation_duration')
                ->label('Durée de consultation (min)'),
            ExportColumn::make('start_working_date')
                ->label('Date de recrutement')
                ->formatStateUsing(fn (mixed $state): string => $state?->format('d/m/Y') ?? ''),
            ExportColumn::make('status')
                ->label('Statut')
                ->formatStateUsing(fn (mixed $state): string => $state instanceof DoctorStatus ? $state->getLabel() : (string) ($state ?? '')),
            ExportColumn::make('created_at')
                ->label('Inscrit le')
                ->formatStateUsing(fn (mixed $state): string => $state?->format('d/m/Y H:i') ?? ''),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        return 'L\'export des médecins est terminé : '.number_format($export->rows_count).' lignes.';
    }
}
