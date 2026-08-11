<?php

namespace App\Filament\Resources\Prescriptions\Actions;

use App\Models\Prescription;
use App\Services\PrescriptionPdfService;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;

class PrintPrescriptionAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'printPrescription')
            ->label('Imprimer')
            ->icon(Heroicon::OutlinedPrinter)
            ->color('gray')
            ->authorize('print')
            ->visible(fn (Action $action): bool => auth()->user()?->can('print', $action->getRecord()) ?? false)
            ->action(function (Action $action) {
                /** @var Prescription $prescription */
                $prescription = $action->getRecord();

                return app(PrescriptionPdfService::class)->download($prescription);
            });
    }
}
