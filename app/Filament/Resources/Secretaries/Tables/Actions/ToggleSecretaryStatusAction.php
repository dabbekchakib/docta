<?php

namespace App\Filament\Resources\Secretaries\Tables\Actions;

use App\Enums\SecretaryStatus;
use App\Models\Secretary;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class ToggleSecretaryStatusAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'toggleStatus')
            ->label(fn (Action $action): string => $action->getRecord()?->status === SecretaryStatus::Active ? 'Désactiver' : 'Réactiver')
            ->icon(fn (Action $action): string => $action->getRecord()?->status === SecretaryStatus::Active ? 'heroicon-m-pause' : 'heroicon-m-play')
            ->color(fn (Action $action): string => $action->getRecord()?->status === SecretaryStatus::Active ? 'warning' : 'success')
            ->requiresConfirmation()
            ->modalHeading('Changer le statut de la secrétaire')
            ->modalDescription(fn (Action $action): string => $action->getRecord()?->status === SecretaryStatus::Active
                ? 'Désactiver cette secrétaire ? Son compte utilisateur sera également bloqué.'
                : 'Réactiver cette secrétaire ? Son compte utilisateur sera débloqué.')
            ->action(function (Action $action): void {
                /** @var Secretary $secretary */
                $secretary = $action->getRecord();

                $newStatus = $secretary->status === SecretaryStatus::Active
                    ? SecretaryStatus::Inactive
                    : SecretaryStatus::Active;

                $secretary->status = $newStatus;
                $secretary->save();

                if ($secretary->user) {
                    $secretary->user->is_active = $newStatus === SecretaryStatus::Active;
                    $secretary->user->save();
                }

                Notification::make()
                    ->title($secretary->fresh()->status === SecretaryStatus::Active ? 'Secrétaire réactivée' : 'Secrétaire désactivée')
                    ->success()
                    ->send();
            });
    }
}
