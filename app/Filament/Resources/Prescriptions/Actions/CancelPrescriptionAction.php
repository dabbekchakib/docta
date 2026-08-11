<?php

namespace App\Filament\Resources\Prescriptions\Actions;

use App\Services\PrescriptionService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class CancelPrescriptionAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'cancelPrescription')
            ->label('Annuler')
            ->icon(Heroicon::OutlinedXCircle)
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Annuler l\'ordonnance')
            ->modalDescription('L\'ordonnance sera clôturée et ne pourra plus être utilisée.')
            ->form([
                Textarea::make('reason')
                    ->label('Motif d\'annulation')
                    ->rows(3)
                    ->required(),
            ])
            ->authorize('cancel')
            ->visible(fn (Action $action): bool => auth()->user()?->can('cancel', $action->getRecord()) ?? false)
            ->action(function (Action $action, array $data): void {
                app(PrescriptionService::class)->cancel($action->getRecord(), $data['reason'] ?? null);

                Notification::make()
                    ->title('Ordonnance annulée')
                    ->success()
                    ->send();
            });
    }
}
