<?php

namespace App\Filament\Resources\Payments\Actions;

use App\Services\PaymentService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class CancelPaymentAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'cancelPayment')
            ->label('Annuler le paiement')
            ->icon(Heroicon::OutlinedXCircle)
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Annuler cet encaissement')
            ->modalDescription('Le reçu lié sera supprimé et le solde de la facture restauré.')
            ->form([
                Textarea::make('reason')
                    ->label('Motif de l\'annulation')
                    ->required()
                    ->rows(2),
            ])
            ->action(function (Action $action, array $data): void {
                app(PaymentService::class)->cancel($action->getRecord(), $data['reason'] ?? null);

                Notification::make()
                    ->title('Paiement annulé')
                    ->success()
                    ->send();
            });
    }
}
