<?php

namespace App\Filament\Resources\CreditNotes\Actions;

use App\Enums\CreditNoteStatus;
use App\Services\CreditNoteService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class IssueCreditNoteAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'issueCreditNote')
            ->label('Émettre l\'avoir')
            ->icon(Heroicon::OutlinedCheckCircle)
            ->color('primary')
            ->requiresConfirmation()
            ->visible(fn (Action $action): bool => auth()->user()?->can('issue', $action->getRecord()) ?? false)
            ->action(function (Action $action): void {
                app(CreditNoteService::class)->issue($action->getRecord());

                Notification::make()
                    ->title('Avoir émis')
                    ->success()
                    ->send();
            });
    }
}
