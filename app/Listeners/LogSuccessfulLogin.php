<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;

class LogSuccessfulLogin
{
    public function handle(Login $event): void
    {
        if (! $event->user) {
            return;
        }

        activity('auth')
            ->performedOn($event->user)
            ->causedBy($event->user)
            ->log('Connexion réussie');
    }
}
