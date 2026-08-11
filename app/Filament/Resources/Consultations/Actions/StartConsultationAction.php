<?php

namespace App\Filament\Resources\Consultations\Actions;

use App\Enums\AppointmentStatus;
use App\Enums\Permission;
use App\Filament\Resources\Consultations\ConsultationResource;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Services\ConsultationService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class StartConsultationAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'startConsultation')
            ->label('Démarrer consultation')
            ->icon(Heroicon::OutlinedPlay)
            ->color('primary')
            ->requiresConfirmation()
            ->modalHeading('Démarrer une consultation')
            ->modalDescription('Une consultation sera créée pour ce rendez-vous et celui-ci passera au statut "En cours".')
            ->visible(fn (Action $action): bool => self::canBeStarted($action))
            ->action(function (Action $action) {
                /** @var Appointment $appointment */
                $appointment = $action->getRecord();

                $consultation = app(ConsultationService::class)->startFromAppointment($appointment);

                Notification::make()
                    ->title('Consultation démarrée')
                    ->body('La consultation '.$consultation->consultation_number.' a été créée.')
                    ->success()
                    ->send();

                return redirect()->to(
                    ConsultationResource::getUrl('edit', ['record' => $consultation->getKey()])
                );
            });
    }

    private static function canBeStarted(Action $action): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if (! $user->hasAnyPermission([
            Permission::ConsultationsCreate->value,
            Permission::ConsultationsManage->value,
        ])) {
            return false;
        }

        $appointment = $action->getRecord();

        if (! $appointment instanceof Appointment) {
            return false;
        }

        if (! in_array($appointment->status, [
            AppointmentStatus::Pending,
            AppointmentStatus::Confirmed,
            AppointmentStatus::Waiting,
            AppointmentStatus::InProgress,
        ], true)) {
            return false;
        }

        if ($appointment->consultation !== null) {
            return false;
        }

        if ($user->hasRole('doctor')) {
            $doctorId = Doctor::query()->where('user_id', $user->id)->value('id');

            return $doctorId !== null && $appointment->doctor_id === $doctorId;
        }

        return true;
    }
}
