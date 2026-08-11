<?php

namespace App\Filament\Resources\Consultations\Exports;

use App\Enums\ConsultationStatus;
use App\Enums\ConsultationType;
use App\Models\Consultation;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class ConsultationExporter extends Exporter
{
    protected static ?string $model = Consultation::class;

    /**
     * @return array<int, ExportColumn>
     */
    public static function getColumns(): array
    {
        return [
            ExportColumn::make('consultation_number')->label('N° consultation'),
            ExportColumn::make('patient.full_name')->label('Patient'),
            ExportColumn::make('doctor.full_name')->label('Médecin'),
            ExportColumn::make('consultation_date')
                ->label('Date')
                ->formatStateUsing(fn (mixed $state): string => $state?->format('d/m/Y') ?? ''),
            ExportColumn::make('type')
                ->label('Type')
                ->formatStateUsing(fn (mixed $state): string => $state instanceof ConsultationType ? $state->getLabel() : (string) ($state ?? '')),
            ExportColumn::make('status')
                ->label('Statut')
                ->formatStateUsing(fn (mixed $state): string => $state instanceof ConsultationStatus ? $state->getLabel() : (string) ($state ?? '')),
            ExportColumn::make('reason')->label('Motif'),
            ExportColumn::make('diagnosis')->label('Diagnostic'),
            ExportColumn::make('follow_up_date')
                ->label('Prochain contrôle')
                ->formatStateUsing(fn (mixed $state): string => $state?->format('d/m/Y') ?? ''),
            ExportColumn::make('created_at')
                ->label('Créé le')
                ->formatStateUsing(fn (mixed $state): string => $state?->format('d/m/Y H:i') ?? ''),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        return 'L\'export des consultations est terminé : '.number_format($export->rows_count).' lignes.';
    }
}
