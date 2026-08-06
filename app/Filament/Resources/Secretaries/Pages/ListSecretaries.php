<?php

namespace App\Filament\Resources\Secretaries\Pages;

use App\Filament\Resources\Secretaries\Actions\HeaderAction;
use App\Filament\Resources\Secretaries\Exports\SecretaryExporter;
use App\Filament\Resources\Secretaries\SecretaryResource;
use Filament\Actions\ExportAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListSecretaries extends ListRecords
{
    protected static string $resource = SecretaryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExportAction::make()
                ->label('Exporter')
                ->icon(Heroicon::ArrowDownTray)
                ->exporter(SecretaryExporter::class)
                ->authorize('export', static::getResource()::getModel()),
            HeaderAction::make(),
        ];
    }
}
