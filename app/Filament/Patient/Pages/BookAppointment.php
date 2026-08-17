<?php

namespace App\Filament\Patient\Pages;

use App\Enums\DoctorStatus;
use App\Filament\Patient\Pages\Concerns\HasPatient;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Services\AppointmentService;
use BackedEnum;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class BookAppointment extends Page implements HasForms
{
    use HasPatient, InteractsWithForms;

    protected string $view = 'filament.patient.pages.book-appointment';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-m-plus-circle';

    protected static string|\UnitEnum|null $navigationGroup = 'Mes soins';

    protected static ?int $navigationSort = 3;

    public ?array $data = [];

    public ?array $availableSlots = [];

    public function getHeading(): string
    {
        return 'Prendre rendez-vous';
    }

    public function mount(): void
    {
        $this->form->fill([
            'appointment_date' => null,
            'doctor_id' => null,
            'start_time' => null,
            'reason' => null,
            'notes' => null,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Nouveau rendez-vous')
                    ->icon('heroicon-m-calendar-days')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('doctor_id')
                                    ->label('Médecin')
                                    ->options(fn () => Doctor::where('status', DoctorStatus::Active)
                                        ->get()
                                        ->mapWithKeys(fn (Doctor $d) => [
                                            $d->id => "{$d->full_name} — {$d->speciality?->getLabel()}",
                                        ]))
                                    ->searchable()
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(fn () => $this->updateAvailableSlots()),

                                DatePicker::make('appointment_date')
                                    ->label('Date du rendez-vous')
                                    ->minDate(now()->toDateString())
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(fn () => $this->updateAvailableSlots()),
                            ]),

                        Grid::make(2)
                            ->schema([
                                Select::make('start_time')
                                    ->label('Créneau horaire')
                                    ->options(fn () => collect($this->availableSlots)
                                        ->mapWithKeys(fn (string $slot) => [
                                            $slot => $slot,
                                        ])
                                        ->all())
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state) {
                                        if ($state) {
                                            $endTime = Carbon::parse($state)->addMinutes(30)->format('H:i');
                                            $this->form->fill(['end_time' => $endTime]);
                                        }
                                    })
                                    ->placeholder('Sélectionnez d\'abord un médecin et une date'),

                                Textarea::make('reason')
                                    ->label('Motif de la consultation')
                                    ->rows(3)
                                    ->placeholder('Décrivez brièvement le motif de votre visite')
                                    ->maxLength(500),
                            ]),

                        Textarea::make('notes')
                            ->label('Notes supplémentaires')
                            ->rows(2)
                            ->placeholder('Informations complémentaires (optionnel)')
                            ->maxLength(500),
                    ]),
            ])
            ->statePath('data');
    }

    public function updateAvailableSlots(): void
    {
        $data = $this->form->getState();
        $doctorId = $data['doctor_id'] ?? null;
        $date = $data['appointment_date'] ?? null;

        if (! $doctorId || ! $date) {
            $this->availableSlots = [];

            return;
        }

        $doctor = Doctor::find($doctorId);

        if (! $doctor) {
            $this->availableSlots = [];

            return;
        }

        $dayOfWeek = Carbon::parse($date)->dayOfWeek;
        $schedule = $doctor->schedules()->where('day_of_week', $dayOfWeek)->first();

        if (! $schedule) {
            $this->availableSlots = [];
            Notification::make()
                ->title('Aucun horaire disponible')
                ->body('Le médecin ne consulte pas ce jour-là.')
                ->warning()
                ->send();

            return;
        }

        $start = Carbon::parse($schedule->start_time);
        $end = Carbon::parse($schedule->end_time);
        $breakStart = $schedule->break_start ? Carbon::parse($schedule->break_start) : null;
        $breakEnd = $schedule->break_end ? Carbon::parse($schedule->break_end) : null;

        $slots = [];
        $current = $start->copy();

        while ($current->copy()->addMinutes(30)->lte($end)) {
            $slotEnd = $current->copy()->addMinutes(30);

            if ($breakStart && $breakEnd && $current->lt($breakEnd) && $slotEnd->gt($breakStart)) {
                $current = $breakEnd->copy();
                continue;
            }

            $appointmentService = app(AppointmentService::class);

            if ($appointmentService->isDoctorAvailable(
                doctor: $doctor,
                date: $date,
                startTime: $current->format('H:i'),
                endTime: $slotEnd->format('H:i'),
            )) {
                $slots[] = $current->format('H:i');
            }

            $current->addMinutes(30);
        }

        $this->availableSlots = $slots;

        if (empty($slots)) {
            Notification::make()
                ->title('Aucun créneau disponible')
                ->body('Tous les créneaux sont occupés pour cette date. Veuillez en choisir une autre.')
                ->warning()
                ->send();
        }
    }

    public function save(): void
    {
        $patient = $this->getPatient();

        if (! $patient) {
            Notification::make()
                ->title('Erreur')
                ->body('Aucun dossier patient trouvé.')
                ->danger()
                ->send();

            return;
        }

        try {
            $data = $this->form->getState();

            $appointmentService = app(AppointmentService::class);

            $timeWindow = $appointmentService->resolveTimeWindow([
                'start_time' => $data['start_time'],
                'duration' => 30,
            ]);

            $appointmentService->create([
                'patient_id' => $patient->id,
                'doctor_id' => $data['doctor_id'],
                'appointment_date' => $data['appointment_date'],
                'start_time' => $timeWindow['start_time'],
                'end_time' => $timeWindow['end_time'],
                'duration' => $timeWindow['duration'],
                'reason' => $data['reason'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => 'pending',
                'type' => 'consultation',
            ]);

            $this->form->fill([
                'doctor_id' => null,
                'appointment_date' => null,
                'start_time' => null,
                'reason' => null,
                'notes' => null,
            ]);

            $this->availableSlots = [];

            Notification::make()
                ->title('Rendez-vous confirmé')
                ->body('Votre demande de rendez-vous a été enregistrée avec succès. Vous recevrez une confirmation sous peu.')
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Erreur')
                ->body($e->getMessage() ?: 'Une erreur est survenue lors de la prise de rendez-vous.')
                ->danger()
                ->send();
        }
    }
}
