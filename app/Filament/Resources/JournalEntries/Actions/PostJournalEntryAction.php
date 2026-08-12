<?php

namespace App\Filament\Resources\JournalEntries\Actions;

use App\Enums\JournalEntryStatus;
use App\Models\JournalEntry;
use App\Services\JournalEntryService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class PostJournalEntryAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'postJournalEntry')
            ->label('Valider l\'écriture')
            ->icon(Heroicon::OutlinedCheckCircle)
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Valider l\'écriture comptable')
            ->modalDescription('L\'écriture sera définitivement saisie au journal et impactera la balance.')
            ->authorize('post')
            ->visible(fn (Action $action): bool => $action->getRecord() instanceof JournalEntry
                && $action->getRecord()->status === JournalEntryStatus::Draft
                && (auth()->user()?->can('post', $action->getRecord()) ?? false))
            ->action(function (Action $action): void {
                app(JournalEntryService::class)->postDraft($action->getRecord());

                Notification::make()
                    ->title('Écriture validée')
                    ->success()
                    ->send();
            });
    }
}
