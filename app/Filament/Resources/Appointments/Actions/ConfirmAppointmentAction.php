<?php

namespace App\Filament\Resources\Appointments\Actions;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Services\AppointmentService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class ConfirmAppointmentAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'confirmAppointment')
            ->label('Confirmer')
            ->icon(Heroicon::OutlinedCheck)
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Confirmer le rendez-vous')
            ->modalDescription('Le patient sera notifié de la confirmation de son rendez-vous.')
            ->authorize('confirm')
            ->visible(fn (Action $action): bool => $action->getRecord()?->status !== null
                && in_array($action->getRecord()->status, [AppointmentStatus::Pending, AppointmentStatus::Waiting], true)
                && auth()->user()?->can('confirm', $action->getRecord()))
            ->action(function (Action $action): void {
                /** @var Appointment $appointment */
                $appointment = $action->getRecord();

                app(AppointmentService::class)->confirm($appointment);

                Notification::make()
                    ->title('Rendez-vous confirmé')
                    ->success()
                    ->send();
            });
    }
}
