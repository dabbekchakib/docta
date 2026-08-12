<?php

namespace App\Filament\Resources\JournalEntries\Actions;

use App\Enums\JournalEntryStatus;
use App\Models\JournalEntry;
use App\Services\JournalEntryService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class CancelJournalEntryAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'cancelJournalEntry')
            ->label('Annuler')
            ->icon(Heroicon::OutlinedXCircle)
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Annuler l\'écriture comptable')
            ->modalDescription('Seule une écriture brouillon peut être annulée.')
            ->form([
                Textarea::make('reason')
                    ->label('Motif d\'annulation')
                    ->rows(3),
            ])
            ->authorize('cancel')
            ->visible(fn (Action $action): bool => $action->getRecord() instanceof JournalEntry
                && $action->getRecord()->status === JournalEntryStatus::Draft
                && (auth()->user()?->can('cancel', $action->getRecord()) ?? false))
            ->action(function (Action $action, array $data): void {
                app(JournalEntryService::class)->cancel($action->getRecord(), $data['reason'] ?? null);

                Notification::make()
                    ->title('Écriture annulée')
                    ->success()
                    ->send();
            });
    }
}
