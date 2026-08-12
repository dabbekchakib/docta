<?php

namespace App\Filament\Resources\Refunds\Actions;

use App\Services\RefundService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class RejectRefundAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'rejectRefund')
            ->label('Refuser')
            ->icon(Heroicon::OutlinedXCircle)
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Refuser ce remboursement')
            ->form([
                Textarea::make('reason')
                    ->label('Motif du refus')
                    ->required()
                    ->rows(2),
            ])
            ->visible(fn (Action $action): bool => auth()->user()?->can('reject', $action->getRecord()) ?? false)
            ->action(function (Action $action, array $data): void {
                app(RefundService::class)->reject($action->getRecord(), $data['reason'] ?? null);

                Notification::make()
                    ->title('Remboursement refusé')
                    ->danger()
                    ->send();
            });
    }
}
