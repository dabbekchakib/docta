<?php

namespace App\Filament\Pages;

use App\Enums\AppointmentStatus;
use App\Filament\Resources\Appointments\AppointmentResource;
use App\Models\Appointment;
use App\Models\Doctor;
use BackedEnum;
use Carbon\CarbonImmutable;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;

class AppointmentsCalendar extends Page
{
    protected string $view = 'filament.pages.appointments-calendar';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendar;

    protected static string|\UnitEnum|null $navigationGroup = 'Gestion';

    protected static ?string $navigationLabel = 'Agenda';

    protected static ?int $navigationSort = 2;

    public string $currentDate;

    public ?int $doctorId = null;

    private ?Collection $appointmentsCache = null;

    public function mount(): void
    {
        $this->currentDate = now()->format('Y-m-d');

        if ($this->isDoctorUser()) {
            $this->doctorId = Doctor::query()->where('user_id', auth()->id())->value('id');
        }
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyPermission(['appointments.calendar', 'appointments.manage']) ?? false;
    }

    public function previousMonth(): void
    {
        $this->currentDate = CarbonImmutable::parse($this->currentDate)->subMonth()->startOfMonth()->format('Y-m-d');
    }

    public function nextMonth(): void
    {
        $this->currentDate = CarbonImmutable::parse($this->currentDate)->addMonth()->startOfMonth()->format('Y-m-d');
    }

    public function goToToday(): void
    {
        $this->currentDate = now()->format('Y-m-d');
    }

    public function getMonthLabel(): string
    {
        $month = (int) CarbonImmutable::parse($this->currentDate)->format('n') - 1;
        $year = CarbonImmutable::parse($this->currentDate)->format('Y');

        $months = [
            'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
            'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre',
        ];

        return $months[$month].' '.$year;
    }

    /**
     * Dates de la grille du mois (du lundi précédant le 1er au dimanche suivant le dernier jour).
     *
     * @return array<int, string>
     */
    public function getCalendarDates(): array
    {
        $month = CarbonImmutable::parse($this->currentDate)->startOfMonth();

        $start = $month->subDays((int) $month->format('N') - 1);
        $end = $month->endOfMonth()->addDays(7 - (int) $month->endOfMonth()->format('N'));

        return collect($start->toPeriod($end, 1, 'days'))
            ->map(fn (CarbonImmutable $date): string => $date->format('Y-m-d'))
            ->all();
    }

    public function isInCurrentMonth(string $date): bool
    {
        return CarbonImmutable::parse($date)->format('m-Y') === CarbonImmutable::parse($this->currentDate)->format('m-Y');
    }

    /**
     * @return Collection<int, Appointment>
     */
    public function appointmentsFor(string $date): Collection
    {
        return $this->appointments()->get($date, collect());
    }

    /**
     * Rendez-vous du mois, groupés par date.
     *
     * @return Collection<string, Collection<int, Appointment>>
     */
    public function appointments(): Collection
    {
        if ($this->appointmentsCache !== null) {
            return $this->appointmentsCache;
        }

        $month = CarbonImmutable::parse($this->currentDate);

        return $this->appointmentsCache = Appointment::query()
            ->with(['patient', 'doctor'])
            ->whereBetween('appointment_date', [$month->startOfMonth()->format('Y-m-d'), $month->endOfMonth()->format('Y-m-d')])
            ->when($this->doctorId, fn ($query) => $query->where('doctor_id', $this->doctorId))
            ->orderBy('start_time')
            ->get()
            ->groupBy('appointment_date');
    }

    public function isDoctorUser(): bool
    {
        return (bool) auth()->user()?->hasRole('doctor');
    }

    public function doctorsForFilter(): array
    {
        return Doctor::query()
            ->where('status', 'active')
            ->orderBy('last_name')
            ->get()
            ->mapWithKeys(fn (Doctor $doctor): array => [$doctor->id => $doctor->full_name])
            ->all();
    }

    public function appointmentStatusColor(Appointment $appointment): string
    {
        return $appointment->status instanceof AppointmentStatus
            ? $appointment->status->calendarColor()
            : AppointmentStatus::Pending->calendarColor();
    }

    public function getCreateAppointmentUrl(): string
    {
        return AppointmentResource::getUrl('create');
    }

    public function getAppointmentUrl(int $id): string
    {
        return AppointmentResource::getUrl('view', ['record' => $id]);
    }
}
