<?php

namespace App\Filament\Resources\Doctors\Tables\Actions;

use App\Enums\DoctorStatus;
use App\Models\Doctor;
use App\Services\DoctorService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class ToggleDoctorStatusAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'toggleStatus')
            ->label(fn (Action $action): string => $action->getRecord()?->status === DoctorStatus::Active ? 'Désactiver' : 'Réactiver')
            ->icon(fn (Action $action): Heroicon => $action->getRecord()?->status === DoctorStatus::Active ? Heroicon::Pause : Heroicon::Play)
            ->color(fn (Action $action): string => $action->getRecord()?->status === DoctorStatus::Active ? 'warning' : 'success')
            ->requiresConfirmation()
            ->modalHeading('Changer le statut du médecin')
            ->modalDescription(fn (Action $action): string => $action->getRecord()?->status === DoctorStatus::Active
                ? 'Désactiver ce médecin ? Il ne pourra plus recevoir de rendez-vous.'
                : 'Réactiver ce médecin ? Il pourra à nouveau recevoir des rendez-vous.')
            ->action(function (Action $action): void {
                /** @var Doctor $doctor */
                $doctor = $action->getRecord();

                $doctor->status === DoctorStatus::Active
                    ? app(DoctorService::class)->deactivate($doctor)
                    : app(DoctorService::class)->reactivate($doctor);

                Notification::make()
                    ->title($doctor->fresh()->status === DoctorStatus::Active ? 'Médecin réactivé' : 'Médecin désactivé')
                    ->success()
                    ->send();
            });
    }
}
