<?php

namespace App\Filament\Resources\Appointments\Schemas;

use App\Enums\AppointmentStatus;
use App\Enums\AppointmentType;
use App\Models\Patient;
use App\Models\Secretary;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class AppointmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Tabs::make('appointment')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Rendez-vous')
                            ->schema([
                                Section::make('Détails du rendez-vous')
                                    ->schema([
                                        Select::make('patient_id')
                                            ->label('Patient')
                                            ->relationship('patient', 'full_name')
                                            ->getSearchResultsUsing(fn (Select $component, ?string $search): array => self::searchPatients($search))
                                            ->getOptionLabelFromRecordUsing(fn (Patient $record): string => $record->full_name.' — '.$record->patient_number)
                                            ->searchable()
                                            ->preload()
                                            ->required(),
                                        Select::make('doctor_id')
                                            ->label('Médecin')
                                            ->relationship('doctor', 'full_name')
                                            ->getSearchResultsUsing(fn (Select $component, ?string $search): array => self::searchDoctors($search))
                                            ->getOptionLabelFromRecordUsing(fn ($record): string => $record->full_name)
                                            ->searchable()
                                            ->preload()
                                            ->required(),
                                        DatePicker::make('appointment_date')
                                            ->label('Date')
                                            ->displayFormat('d/m/Y')
                                            ->afterOrEqual('today')
                                            ->native(false)
                                            ->required(),
                                        TimePicker::make('start_time')
                                            ->label('Heure de début')
                                            ->seconds(false)
                                            ->required(),
                                        Select::make('duration')
                                            ->label('Durée (minutes)')
                                            ->options([
                                                15 => '15 min',
                                                20 => '20 min',
                                                30 => '30 min',
                                                45 => '45 min',
                                                60 => '60 min',
                                            ])
                                            ->default(30)
                                            ->reactive()
                                            ->required(),
                                        Placeholder::make('end_time_preview')
                                            ->label('Fin estimée')
                                            ->content(fn (Get $get): string => self::previewEndTime($get)),
                                        Select::make('type')
                                            ->label('Type')
                                            ->options(AppointmentType::options())
                                            ->default(AppointmentType::Consultation->value)
                                            ->required(),
                                        Select::make('status')
                                            ->label('Statut')
                                            ->options(AppointmentStatus::options())
                                            ->default(AppointmentStatus::Pending->value)
                                            ->required(),
                                        Textarea::make('reason')
                                            ->label('Motif')
                                            ->rows(3)
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(3),
                            ]),
                        Tab::make('Notes')
                            ->schema([
                                Section::make('Notes')
                                    ->schema([
                                        Textarea::make('notes')
                                            ->label('Notes internes')
                                            ->rows(6)
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                        Tab::make('Suivi')
                            ->schema([
                                Section::make('Suivi')
                                    ->schema([
                                        Select::make('secretary_id')
                                            ->label('Secrétaire')
                                            ->relationship('secretary', 'full_name')
                                            ->getOptionLabelFromRecordUsing(fn ($record): string => $record->full_name)
                                            ->searchable()
                                            ->preload()
                                            ->nullable()
                                            ->default(fn (): ?int => Secretary::query()->where('user_id', Auth::id())->value('id')),
                                        Placeholder::make('appointment_number')
                                            ->label('N° rendez-vous')
                                            ->content(fn (Placeholder $component): string => $component->getRecord()?->appointment_number ?? 'RDV-xxxxxx (généré automatiquement)'),
                                        Placeholder::make('confirmed_at')
                                            ->label('Confirmé le')
                                            ->content(fn (Placeholder $component): string => self::formatTimestamp($component->getRecord()?->confirmed_at)),
                                        Placeholder::make('cancelled_at')
                                            ->label('Annulé le')
                                            ->content(fn (Placeholder $component): string => self::formatTimestamp($component->getRecord()?->cancelled_at)),
                                        Placeholder::make('completed_at')
                                            ->label('Terminé le')
                                            ->content(fn (Placeholder $component): string => self::formatTimestamp($component->getRecord()?->completed_at)),
                                    ])
                                    ->columns(2),
                            ]),
                    ]),
            ]);
    }

    /**
     * @return array<int, string>
     */
    private static function searchPatients(?string $search): array
    {
        return Patient::query()
            ->when($search, function ($query, string $search): void {
                $query->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('patient_number', 'like', "%{$search}%")
                    ->orWhere('cin', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            })
            ->limit(20)
            ->get()
            ->mapWithKeys(fn (Patient $patient): array => [
                $patient->id => $patient->full_name.' — '.$patient->patient_number,
            ])
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private static function searchDoctors(?string $search): array
    {
        return \App\Models\Doctor::query()
            ->when($search, function ($query, string $search): void {
                $query->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('doctor_code', 'like', "%{$search}%");
            })
            ->limit(20)
            ->get()
            ->mapWithKeys(fn ($doctor): array => [
                $doctor->id => $doctor->full_name,
            ])
            ->all();
    }

    private static function previewEndTime(Get $get): string
    {
        $start = $get('start_time');
        $duration = (int) ($get('duration') ?? 30);

        if (! $start) {
            return 'Calculée automatiquement';
        }

        return \Carbon\Carbon::parse($start)->addMinutes($duration)->format('H:i');
    }

    private static function formatTimestamp(mixed $value): string
    {
        return $value?->format('d/m/Y H:i') ?? '—';
    }
}
