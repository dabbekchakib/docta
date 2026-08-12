<?php

namespace App\Filament\Resources\CreditNotes\Actions;

use App\Services\CreditNoteService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class CancelCreditNoteAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'cancelCreditNote')
            ->label('Annuler l\'avoir')
            ->icon(Heroicon::OutlinedXCircle)
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Annuler cet avoir')
            ->form([
                Textarea::make('reason')
                    ->label('Motif de l\'annulation')
                    ->required()
                    ->rows(2),
            ])
            ->visible(fn (Action $action): bool => auth()->user()?->can('cancel', $action->getRecord()) ?? false)
            ->action(function (Action $action, array $data): void {
                app(CreditNoteService::class)->cancel($action->getRecord(), $data['reason'] ?? null);

                Notification::make()
                    ->title('Avoir annulé')
                    ->success()
                    ->send();
            });
    }
}
