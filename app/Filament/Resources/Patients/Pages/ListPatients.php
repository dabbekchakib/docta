<?php

namespace App\Filament\Resources\Patients\Pages;

use App\Filament\Resources\Patients\Exports\PatientExporter;
use App\Filament\Resources\Patients\PatientResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Resources\Pages\ListRecords;

class ListPatients extends ListRecords
{
    protected static string $resource = PatientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExportAction::make()
                ->label('Exporter')
                ->exporter(PatientExporter::class),
            CreateAction::make(),
        ];
    }
}
