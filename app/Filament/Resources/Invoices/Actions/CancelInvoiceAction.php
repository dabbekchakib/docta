<?php

namespace App\Filament\Resources\Invoices\Actions;

use App\Services\InvoiceService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class CancelInvoiceAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'cancelInvoice')
            ->label('Annuler')
            ->icon(Heroicon::OutlinedXCircle)
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Annuler la facture')
            ->modalDescription('Une facture annulée ne peut plus être encaissée ni modifiée.')
            ->form([
                Textarea::make('reason')
                    ->label('Motif d\'annulation')
                    ->rows(3)
                    ->required(),
            ])
            ->authorize('cancel')
            ->visible(fn (Action $action): bool => auth()->user()?->can('cancel', $action->getRecord()) ?? false)
            ->action(function (Action $action, array $data): void {
                app(InvoiceService::class)->cancel($action->getRecord(), $data['reason'] ?? null);

                Notification::make()
                    ->title('Facture annulée')
                    ->success()
                    ->send();
            });
    }
}
