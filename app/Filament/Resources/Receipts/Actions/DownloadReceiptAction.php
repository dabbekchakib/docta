<?php

namespace App\Filament\Resources\Receipts\Actions;

use App\Services\ReceiptPdfService;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;

class DownloadReceiptAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'downloadReceipt')
            ->label('Télécharger le reçu')
            ->icon(Heroicon::OutlinedArrowDownTray)
            ->color('gray')
            ->authorize('download')
            ->visible(fn (Action $action): bool => auth()->user()?->can('download', $action->getRecord()) ?? false)
            ->action(fn (Action $action) => app(ReceiptPdfService::class)->download($action->getRecord()));
    }
}
