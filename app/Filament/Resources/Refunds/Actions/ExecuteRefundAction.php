<?php

namespace App\Filament\Resources\Refunds\Actions;

use App\Services\RefundService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class ExecuteRefundAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'executeRefund')
            ->label('Exécuter le remboursement')
            ->icon(Heroicon::OutlinedCheckBadge)
            ->color('primary')
            ->requiresConfirmation()
            ->modalHeading('Exécuter ce remboursement')
            ->modalDescription('Le paiement sera soldé et, le cas échéant, l\'avoir associé clôturé.')
            ->visible(fn (Action $action): bool => auth()->user()?->can('approve', $action->getRecord()) ?? false)
            ->action(function (Action $action): void {
                app(RefundService::class)->execute($action->getRecord());

                Notification::make()
                    ->title('Remboursement exécuté')
                    ->success()
                    ->send();
            });
    }
}
