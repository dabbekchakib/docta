<?php

namespace App\Filament\Patient\Pages;

use App\Models\Patient;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getHeading(): string
    {
        return 'Tableau de bord';
    }
}
