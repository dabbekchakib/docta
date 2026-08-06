<?php

namespace App\Filament\Widgets;

use App\Enums\MedicalSpecialty;
use App\Models\Doctor;
use Filament\Widgets\ChartWidget;

class SpecialtiesDistributionWidget extends ChartWidget
{
    protected ?string $heading = 'Répartition des médecins par spécialité';

    protected static ?int $sort = 5;

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $labels = [];
        $values = [];

        foreach (MedicalSpecialty::cases() as $specialty) {
            $count = Doctor::query()->where('speciality', $specialty->value)->count();

            if ($count === 0) {
                continue;
            }

            $labels[] = $specialty->getLabel();
            $values[] = $count;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Médecins',
                    'data' => $values,
                    'backgroundColor' => 'rgba(14, 165, 233, 0.7)',
                    'borderColor' => '#0ea5e9',
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $labels,
        ];
    }
}
