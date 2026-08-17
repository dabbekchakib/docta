<?php

namespace App\Filament\Pages\Rapports;

use App\Enums\PatientGender;
use App\Enums\PatientStatus;
use App\Models\Patient;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class PatientDashboard extends Page
{
    protected string $view = 'filament.pages.rapports.patient-dashboard';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|\UnitEnum|null $navigationGroup = 'Rapports et tableaux de bord';

    protected static ?string $navigationLabel = 'Tableau de bord patients';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Tableau de bord patients';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyPermission([
            'patients.view',
            'reports.view',
        ]) ?? false;
    }

    public function getStats(): array
    {
        $total = Patient::query()->count();
        $active = Patient::query()->where('status', PatientStatus::Active)->count();
        $inactive = Patient::query()->where('status', PatientStatus::Inactive)->count();
        $archived = Patient::query()->where('status', PatientStatus::Archived)->count();

        $newThisMonth = Patient::query()->where('created_at', '>=', now()->startOfMonth())->count();
        $newThisWeek = Patient::query()->where('created_at', '>=', now()->startOfWeek())->count();

        $males = Patient::query()->where('gender', PatientGender::Male)->count();
        $females = $total - $males;

        $withCnam = Patient::query()->where('has_cnam', true)->count();
        $withInsurance = Patient::query()->where('has_insurance', true)->count();

        $newByMonth = Patient::query()
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->get()
            ->groupBy(fn (Patient $p) => $p->created_at->format('M Y'))
            ->map(fn ($items) => $items->count())
            ->toArray();

        $byGovernorate = Patient::query()
            ->whereNotNull('governorate')
            ->get()
            ->groupBy(fn (Patient $p) => $p->governorate->getLabel())
            ->map(fn ($items) => $items->count())
            ->sortDesc()
            ->take(10)
            ->toArray();

        $ageGroups = $this->getAgeGroups();

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
            'archived' => $archived,
            'new_this_month' => $newThisMonth,
            'new_this_week' => $newThisWeek,
            'males' => $males,
            'females' => $females,
            'with_cnam' => $withCnam,
            'with_insurance' => $withInsurance,
            'new_by_month' => $newByMonth,
            'by_governorate' => $byGovernorate,
            'age_groups' => $ageGroups,
        ];
    }

    private function getAgeGroups(): array
    {
        $patients = Patient::query()->whereNotNull('birth_date')->get();

        $groups = [
            '0-10' => 0,
            '11-20' => 0,
            '21-30' => 0,
            '31-40' => 0,
            '41-50' => 0,
            '51-60' => 0,
            '61-70' => 0,
            '71+' => 0,
        ];

        foreach ($patients as $patient) {
            $age = $patient->age;
            if ($age <= 10) {
                $groups['0-10']++;
            } elseif ($age <= 20) {
                $groups['11-20']++;
            } elseif ($age <= 30) {
                $groups['21-30']++;
            } elseif ($age <= 40) {
                $groups['31-40']++;
            } elseif ($age <= 50) {
                $groups['41-50']++;
            } elseif ($age <= 60) {
                $groups['51-60']++;
            } elseif ($age <= 70) {
                $groups['61-70']++;
            } else {
                $groups['71+']++;
            }
        }

        return $groups;
    }
}
