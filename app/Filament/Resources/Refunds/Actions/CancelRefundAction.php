<?php

namespace App\Filament\Resources\Refunds\Actions;

use App\Services\RefundService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class CancelRefundAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'cancelRefund')
            ->label('Annuler la demande')
            ->icon(Heroicon::OutlinedXMark)
            ->color('gray')
            ->requiresConfirmation()
            ->modalHeading('Annuler ce remboursement')
            ->form([
                Textarea::make('reason')
                    ->label('Motif de l\'annulation')
                    ->required()
                    ->rows(2),
            ])
            ->visible(fn (Action $action): bool => auth()->user()?->can('approve', $action->getRecord()) ?? false)
            ->action(function (Action $action, array $data): void {
                app(RefundService::class)->cancel($action->getRecord(), $data['reason'] ?? null);

                Notification::make()
                    ->title('Remboursement annulé')
                    ->success()
                    ->send();
            });
    }
}
