<?php

use App\Providers\AppServiceProvider;
use App\Providers\EventServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\Filament\PatientPanelProvider;

return [
    AppServiceProvider::class,
    EventServiceProvider::class,
    AdminPanelProvider::class,
    PatientPanelProvider::class,
];
