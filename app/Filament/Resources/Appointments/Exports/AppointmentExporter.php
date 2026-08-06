<?php

namespace App\Filament\Resources\Appointments\Exports;

use App\Enums\AppointmentStatus;
use App\Enums\AppointmentType;
use App\Models\Appointment;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class AppointmentExporter extends Exporter
{
    protected static ?string $model = Appointment::class;

    /**
     * @return array<int, ExportColumn>
     */
    public static function getColumns(): array
    {
        return [
            ExportColumn::make('appointment_number')->label('N° RDV'),
            ExportColumn::make('patient.full_name')->label('Patient'),
            ExportColumn::make('doctor.full_name')->label('Médecin'),
            ExportColumn::make('appointment_date')
                ->label('Date')
                ->formatStateUsing(fn (mixed $state): string => $state?->format('d/m/Y') ?? ''),
            ExportColumn::make('start_time')->label('Début'),
            ExportColumn::make('end_time')->label('Fin'),
            ExportColumn::make('duration')->label('Durée (min)'),
            ExportColumn::make('type')
                ->label('Type')
                ->formatStateUsing(fn (mixed $state): string => $state instanceof AppointmentType ? $state->getLabel() : (string) ($state ?? '')),
            ExportColumn::make('status')
                ->label('Statut')
                ->formatStateUsing(fn (mixed $state): string => $state instanceof AppointmentStatus ? $state->getLabel() : (string) ($state ?? '')),
            ExportColumn::make('reason')->label('Motif'),
            ExportColumn::make('created_at')
                ->label('Créé le')
                ->formatStateUsing(fn (mixed $state): string => $state?->format('d/m/Y H:i') ?? ''),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        return 'L\'export des rendez-vous est terminé : '.number_format($export->rows_count).' lignes.';
    }
}
