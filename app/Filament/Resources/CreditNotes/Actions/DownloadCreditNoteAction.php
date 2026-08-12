<?php

namespace App\Filament\Resources\CreditNotes\Actions;

use App\Models\CreditNote;
use App\Services\CreditNotePdfService;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;

class DownloadCreditNoteAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'downloadCreditNote')
            ->label('Télécharger l\'avoir')
            ->icon(Heroicon::OutlinedArrowDownTray)
            ->color('gray')
            ->authorize('download')
            ->visible(fn (Action $action): bool => auth()->user()?->can('download', $action->getRecord()) ?? false)
            ->action(fn (Action $action) => app(CreditNotePdfService::class)->download($action->getRecord()));
    }
}
