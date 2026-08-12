<?php

namespace App\Filament\Resources\Payments\Actions;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Services\PaymentService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class ValidatePaymentAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'validatePayment')
            ->label('Valider le paiement')
            ->icon(Heroicon::OutlinedCheckCircle)
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Valider ce paiement')
            ->modalDescription('Le paiement sera encaissé, un reçu émis automatiquement et la facture mise à jour.')
            ->authorize('validate')
            ->visible(fn (Action $action): bool => self::canValidate($action))
            ->action(function (Action $action): void {
                app(PaymentService::class)->validate($action->getRecord());

                Notification::make()
                    ->title('Paiement validé')
                    ->body('Reçu émis automatiquement.')
                    ->success()
                    ->send();
            });
    }

    private static function canValidate(Action $action): bool
    {
        $payment = $action->getRecord();

        if (! $payment instanceof Payment) {
            return false;
        }

        return $payment->status === PaymentStatus::Pending
            && (auth()->user()?->can('validate', $payment) ?? false);
    }
}
