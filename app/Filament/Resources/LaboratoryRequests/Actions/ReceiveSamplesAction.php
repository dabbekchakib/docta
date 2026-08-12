<?php

namespace App\Filament\Resources\LaboratoryRequests\Actions;

use App\Services\LaboratoryRequestService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class ReceiveSamplesAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'receiveSamples')
            ->label('Réceptionner les prélèvements')
            ->icon(Heroicon::OutlinedArrowDownCircle)
            ->color('info')
            ->requiresConfirmation()
            ->modalHeading('Réceptionner les prélèvements')
            ->modalDescription('Tous les prélèvements collectés seront marqués comme reçus au laboratoire.')
            ->authorize('manageSamples')
            ->visible(fn (Action $action): bool => self::canReceive($action))
            ->action(function (Action $action): void {
                $count = app(LaboratoryRequestService::class)->receiveSamples($action->getRecord());

                Notification::make()
                    ->title($count > 0 ? 'Prélèvements réceptionnés' : 'Aucun prélèvement à réceptionner')
                    ->success()
                    ->send();
            });
    }

    private static function canReceive(Action $action): bool
    {
        $request = $action->getRecord();

        if (! $request || ! auth()->user()?->can('manageSamples', $request)) {
            return false;
        }

        return $request->samples()->where('status', 'collected')->exists();
    }
}
