<?php

namespace App\Filament\Patient\Pages;

use App\Models\Patient;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public static function getNavigationLabel(): string
    {
        return 'Tableau de bord';
    }

    public function getHeading(): string
    {
        return 'Tableau de bord';
    }
}
