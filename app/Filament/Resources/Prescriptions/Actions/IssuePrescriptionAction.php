<?php

namespace App\Filament\Resources\Prescriptions\Actions;

use App\Services\PrescriptionService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class IssuePrescriptionAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'issuePrescription')
            ->label('Émettre')
            ->icon(Heroicon::OutlinedCheckCircle)
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Émettre l\'ordonnance')
            ->modalDescription('L\'ordonnance deviendra officielle et ne pourra plus être modifiée.')
            ->authorize('issue')
            ->visible(fn (Action $action): bool => auth()->user()?->can('issue', $action->getRecord()) ?? false)
            ->action(function (Action $action): void {
                app(PrescriptionService::class)->issue($action->getRecord());

                Notification::make()
                    ->title('Ordonnance émise')
                    ->success()
                    ->send();
            });
    }
}
