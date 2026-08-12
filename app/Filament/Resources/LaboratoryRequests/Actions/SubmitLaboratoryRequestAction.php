<?php

namespace App\Filament\Resources\LaboratoryRequests\Actions;

use App\Services\LaboratoryRequestService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class SubmitLaboratoryRequestAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'submitLaboratoryRequest')
            ->label('Transmettre')
            ->icon(Heroicon::OutlinedPaperAirplane)
            ->color('info')
            ->requiresConfirmation()
            ->modalHeading('Transmettre la demande')
            ->modalDescription('La demande sera envoyée au laboratoire et ne pourra plus être modifiée.')
            ->authorize('submit')
            ->visible(fn (Action $action): bool => auth()->user()?->can('submit', $action->getRecord()) ?? false)
            ->action(function (Action $action): void {
                app(LaboratoryRequestService::class)->submit($action->getRecord());

                Notification::make()
                    ->title('Demande transmise')
                    ->success()
                    ->send();
            });
    }
}
