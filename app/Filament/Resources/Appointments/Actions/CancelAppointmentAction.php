<?php

namespace App\Filament\Resources\Appointments\Actions;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Services\AppointmentService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class CancelAppointmentAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'cancelAppointment')
            ->label('Annuler')
            ->icon(Heroicon::OutlinedXMark)
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Annuler le rendez-vous')
            ->modalDescription('Le patient sera notifié de l\'annulation de son rendez-vous.')
            ->form([
                Textarea::make('reason')
                    ->label('Motif d\'annulation')
                    ->required()
                    ->rows(3),
            ])
            ->authorize('cancel')
            ->visible(fn (Action $action): bool => $action->getRecord()?->status !== null
                && ! in_array($action->getRecord()->status, [
                    AppointmentStatus::Cancelled,
                    AppointmentStatus::Completed,
                    AppointmentStatus::Absent,
                ], true)
                && auth()->user()?->can('cancel', $action->getRecord()))
            ->action(function (Action $action): void {
                /** @var Appointment $appointment */
                $appointment = $action->getRecord();

                app(AppointmentService::class)->cancel($appointment, $action->getFormData()['reason'] ?? null);

                Notification::make()
                    ->title('Rendez-vous annulé')
                    ->success()
                    ->send();
            });
    }
}
