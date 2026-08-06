<?php

namespace App\Filament\Resources\Patients\Exports;

use App\Enums\Governorate;
use App\Enums\PatientGender;
use App\Enums\PatientStatus;
use App\Enums\PatientTitle;
use App\Models\Patient;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class PatientExporter extends Exporter
{
    protected static ?string $model = Patient::class;

    /**
     * @return array<int, ExportColumn>
     */
    public static function getColumns(): array
    {
        return [
            ExportColumn::make('patient_number')->label('N° dossier'),
            ExportColumn::make('full_name')->label('Nom complet'),
            ExportColumn::make('title')
                ->label('Civilité')
                ->formatStateUsing(fn (mixed $state): string => $state instanceof PatientTitle ? $state->getLabel() : (string) ($state ?? '')),
            ExportColumn::make('gender')
                ->label('Sexe')
                ->formatStateUsing(fn (mixed $state): string => $state instanceof PatientGender ? $state->getLabel() : (string) ($state ?? '')),
            ExportColumn::make('birth_date')
                ->label('Date de naissance')
                ->formatStateUsing(fn (mixed $state): string => $state?->format('d/m/Y') ?? ''),
            ExportColumn::make('age')->label('Âge'),
            ExportColumn::make('cin')->label('CIN'),
            ExportColumn::make('phone')->label('Téléphone'),
            ExportColumn::make('phone_secondary')->label('Téléphone secondaire'),
            ExportColumn::make('email')->label('Email'),
            ExportColumn::make('city')->label('Ville'),
            ExportColumn::make('governorate')
                ->label('Gouvernorat')
                ->formatStateUsing(fn (mixed $state): string => $state instanceof Governorate ? $state->getLabel() : (string) ($state ?? '')),
            ExportColumn::make('blood_group')->label('Groupe sanguin'),
            ExportColumn::make('cnam_number')->label('N° CNAM'),
            ExportColumn::make('insurance_number')->label('N° assuré'),
            ExportColumn::make('status')
                ->label('Statut')
                ->formatStateUsing(fn (mixed $state): string => $state instanceof PatientStatus ? $state->getLabel() : (string) ($state ?? '')),
            ExportColumn::make('created_at')
                ->label('Créé le')
                ->formatStateUsing(fn (mixed $state): string => $state?->format('d/m/Y H:i') ?? ''),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        return 'L\'export des patients est terminé : '.number_format($export->rows_count).' lignes.';
    }
}
