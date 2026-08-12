<?php

namespace App\Filament\Resources\Invoices\Actions;

use App\Services\InvoicePdfService;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;

class DownloadInvoiceAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'downloadInvoice')
            ->label('Télécharger la facture')
            ->icon(Heroicon::OutlinedArrowDownTray)
            ->color('gray')
            ->authorize('download')
            ->visible(fn (Action $action): bool => auth()->user()?->can('download', $action->getRecord()) ?? false)
            ->action(fn (Action $action) => app(InvoicePdfService::class)->download($action->getRecord()));
    }
}
