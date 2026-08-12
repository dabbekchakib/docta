<?php

namespace App\Filament\Resources\Payments\Actions;

use App\Enums\PaymentStatus;
use App\Services\ReceiptPdfService;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;

class DownloadPaymentReceiptAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'downloadReceipt')
            ->label('Télécharger le reçu')
            ->icon(Heroicon::OutlinedArrowDownTray)
            ->color('gray')
            ->visible(fn (Action $action): bool => auth()->user()?->can('download', $action->getRecord()) ?? false)
            ->action(fn (Action $action) => app(ReceiptPdfService::class)->downloadForPayment($action->getRecord()));
    }
}
