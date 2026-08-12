<?php

namespace App\Filament\Resources\LaboratoryRequests\Actions;

use App\Services\LaboratoryRequestService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class CancelLaboratoryRequestAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'cancelLaboratoryRequest')
            ->label('Annuler')
            ->icon(Heroicon::OutlinedXCircle)
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Annuler la demande d\'examen')
            ->modalDescription('La demande sera clôturée et les examens ne seront pas réalisés.')
            ->form([
                Textarea::make('reason')
                    ->label('Motif d\'annulation')
                    ->rows(3)
                    ->required(),
            ])
            ->authorize('cancel')
            ->visible(fn (Action $action): bool => auth()->user()?->can('cancel', $action->getRecord()) ?? false)
            ->action(function (Action $action, array $data): void {
                app(LaboratoryRequestService::class)->cancel($action->getRecord(), $data['reason'] ?? null);

                Notification::make()
                    ->title('Demande annulée')
                    ->success()
                    ->send();
            });
    }
}
