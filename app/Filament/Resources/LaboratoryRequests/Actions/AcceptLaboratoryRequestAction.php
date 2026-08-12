<?php

namespace App\Filament\Resources\LaboratoryRequests\Actions;

use App\Services\LaboratoryRequestService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class AcceptLaboratoryRequestAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'acceptLaboratoryRequest')
            ->label('Accepter')
            ->icon(Heroicon::OutlinedCheckCircle)
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Accepter la demande')
            ->modalDescription('La demande est prise en charge par le laboratoire.')
            ->authorize('accept')
            ->visible(fn (Action $action): bool => auth()->user()?->can('accept', $action->getRecord()) ?? false)
            ->action(function (Action $action): void {
                app(LaboratoryRequestService::class)->accept($action->getRecord());

                Notification::make()
                    ->title('Demande acceptée')
                    ->success()
                    ->send();
            });
    }
}
