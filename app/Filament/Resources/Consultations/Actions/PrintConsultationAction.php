<?php

namespace App\Filament\Resources\Consultations\Actions;

use App\Models\Consultation;
use App\Services\ConsultationPdfService;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;

class PrintConsultationAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'printConsultation')
            ->label('Imprimer')
            ->icon(Heroicon::OutlinedPrinter)
            ->color('gray')
            ->authorize('print')
            ->visible(fn (Action $action): bool => auth()->user()?->can('print', $action->getRecord()))
            ->action(function (Action $action) {
                /** @var Consultation $consultation */
                $consultation = $action->getRecord();

                return app(ConsultationPdfService::class)->download($consultation);
            });
    }
}
