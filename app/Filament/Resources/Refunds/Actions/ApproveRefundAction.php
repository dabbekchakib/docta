<?php

namespace App\Filament\Resources\Refunds\Actions;

use App\Services\RefundService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class ApproveRefundAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'approveRefund')
            ->label('Approuver')
            ->icon(Heroicon::OutlinedCheckCircle)
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Approuver ce remboursement')
            ->visible(fn (Action $action): bool => auth()->user()?->can('approve', $action->getRecord()) ?? false)
            ->action(function (Action $action): void {
                app(RefundService::class)->approve($action->getRecord());

                Notification::make()
                    ->title('Remboursement approuvé')
                    ->success()
                    ->send();
            });
    }
}
