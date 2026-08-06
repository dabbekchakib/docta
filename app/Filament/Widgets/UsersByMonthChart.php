<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class UsersByMonthChart extends ChartWidget
{
    protected ?string $heading = 'Nouveaux utilisateurs (12 derniers mois)';

    protected static ?int $sort = 2;

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $labels = [];
        $values = [];

        foreach (range(11, 0) as $offset) {
            $month = Carbon::now()->startOfMonth()->subMonths($offset);
            $labels[] = $month->translatedFormat('M Y');
            $values[] = User::whereBetween('created_at', [
                $month->copy()->startOfMonth(),
                $month->copy()->endOfMonth(),
            ])->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Utilisateurs créés',
                    'data' => $values,
                    'borderColor' => '#0ea5e9',
                    'backgroundColor' => 'rgba(14, 165, 233, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }
}
