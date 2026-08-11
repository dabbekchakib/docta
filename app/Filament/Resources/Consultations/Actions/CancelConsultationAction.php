<?php

namespace App\Filament\Resources\Consultations\Actions;

use App\Enums\ConsultationStatus;
use App\Models\Consultation;
use App\Services\ConsultationService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class CancelConsultationAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'cancelConsultation')
            ->label('Annuler la consultation')
            ->icon(Heroicon::OutlinedXMark)
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Annuler la consultation')
            ->modalDescription('La consultation sera annulée et conservée dans l\'historique.')
            ->form([
                Textarea::make('reason')
                    ->label('Motif d\'annulation')
                    ->required()
                    ->rows(3),
            ])
            ->authorize('update')
            ->visible(fn (Action $action): bool => $action->getRecord()?->status !== null
                && ! in_array($action->getRecord()->status, [ConsultationStatus::Completed, ConsultationStatus::Cancelled], true)
                && auth()->user()?->can('update', $action->getRecord()))
            ->action(function (Action $action): void {
                /** @var Consultation $consultation */
                $consultation = $action->getRecord();

                app(ConsultationService::class)->cancel($consultation, $action->getFormData()['reason'] ?? null);

                Notification::make()
                    ->title('Consultation annulée')
                    ->success()
                    ->send();
            });
    }
}
