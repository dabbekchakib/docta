<?php

namespace App\Filament\Resources\MedicalRecords\Actions;

use App\Models\MedicalRecord;
use App\Services\MedicalRecordPdfService;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;

class PrintMedicalRecordAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'printMedicalRecord')
            ->label('Exporter PDF')
            ->icon(Heroicon::OutlinedArrowDownTray)
            ->color('primary')
            ->authorize('export')
            ->visible(fn (Action $action): bool => auth()->user()?->can('export', $action->getRecord()))
            ->action(function (Action $action) {
                /** @var MedicalRecord $record */
                $record = $action->getRecord();

                return app(MedicalRecordPdfService::class)->download($record);
            });
    }
}
