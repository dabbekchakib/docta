<?php

namespace App\Filament\Resources\Consultations\Actions;

use App\Enums\ConsultationStatus;
use App\Models\Consultation;
use App\Services\ConsultationService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class CompleteConsultationAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'completeConsultation')
            ->label('Terminer la consultation')
            ->icon(Heroicon::OutlinedCheckCircle)
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Terminer la consultation')
            ->modalDescription('Le rendez-vous lié sera également marqué comme terminé.')
            ->authorize('update')
            ->visible(fn (Action $action): bool => $action->getRecord()?->status !== null
                && in_array($action->getRecord()->status, [ConsultationStatus::Scheduled, ConsultationStatus::InProgress], true)
                && auth()->user()?->can('update', $action->getRecord()))
            ->action(function (Action $action): void {
                /** @var Consultation $consultation */
                $consultation = $action->getRecord();

                app(ConsultationService::class)->complete($consultation);

                Notification::make()
                    ->title('Consultation terminée')
                    ->success()
                    ->send();
            });
    }
}
