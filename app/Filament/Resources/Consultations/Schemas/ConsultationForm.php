<?php

namespace App\Filament\Resources\Consultations\Schemas;

use App\Enums\ConsultationStatus;
use App\Enums\ConsultationType;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class ConsultationForm
{
    /** @var array<int, Patient> */
    private static array $patientCache = [];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Tabs::make('consultation')
                    ->columnSpanFull()
                    ->tabs([
                        self::patientTab(),
                        self::consultationTab(),
                        self::vitalSignsTab(),
                        self::diagnosisTab(),
                        self::treatmentTab(),
                        self::documentsTab(),
                    ]),
            ]);
    }

    private static function patientTab(): Tab
    {
        return Tab::make('Patient')
            ->icon('heroicon-o-identification')
            ->schema([
                Section::make('Informations du patient')
                    ->description('Données lues depuis le dossier du patient.')
                    ->schema([
                        Placeholder::make('patient_identity')
                            ->label('Patient')
                            ->content(fn (Get $get): string => self::patientName($get)),
                        Placeholder::make('patient_age')
                            ->label('Âge')
                            ->content(fn (Get $get): string => self::patientAge($get)),
                        Placeholder::make('patient_gender')
                            ->label('Sexe')
                            ->content(fn (Get $get): string => self::patientGender($get)),
                        Placeholder::make('patient_blood_group')
                            ->label('Groupe sanguin')
                            ->content(fn (Get $get): string => self::patientBloodGroup($get)),
                        Placeholder::make('patient_allergies')
                            ->label('Allergies')
                            ->content(fn (Get $get): string => self::patientAllergies($get))
                            ->columnSpanFull(),
                        Placeholder::make('patient_history')
                            ->label('Antécédents')
                            ->content(fn (Get $get): string => self::patientHistory($get))
                            ->columnSpanFull(),
                        Placeholder::make('patient_chronic')
                            ->label('Maladies chroniques')
                            ->content(fn (Get $get): string => self::patientChronicDiseases($get))
                            ->columnSpanFull(),
                    ])
                    ->columns(4),
            ]);
    }

    private static function consultationTab(): Tab
    {
        return Tab::make('Consultation')
            ->icon('heroicon-o-clipboard-document')
            ->schema([
                Section::make('Consultation')
                    ->schema([
                        Select::make('patient_id')
                            ->label('Patient')
                            ->relationship('patient', 'full_name')
                            ->getSearchResultsUsing(fn (Select $component, ?string $search): array => self::searchPatients($search))
                            ->getOptionLabelFromRecordUsing(fn (Patient $record): string => $record->full_name.' — '.$record->patient_number)
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live(),
                        Select::make('doctor_id')
                            ->label('Médecin')
                            ->relationship('doctor', 'full_name')
                            ->getSearchResultsUsing(fn (Select $component, ?string $search): array => self::searchDoctors($search))
                            ->getOptionLabelFromRecordUsing(fn (Doctor $record): string => $record->full_name)
                            ->searchable()
                            ->preload()
                            ->required()
                            ->default(fn (): ?int => self::defaultDoctorId())
                            ->disabled(fn (): bool => (bool) auth()->user()?->hasRole('doctor')),
                        Select::make('appointment_id')
                            ->label('Rendez-vous lié')
                            ->relationship('appointment', 'appointment_number')
                            ->getSearchResultsUsing(fn (Select $component, ?string $search): array => self::searchAppointments($search))
                            ->getOptionLabelFromRecordUsing(fn (Appointment $record): string => $record->appointment_number.' — '.$record->patient?->full_name.' ('.$record->start_time.')')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        DatePicker::make('consultation_date')
                            ->label('Date de consultation')
                            ->displayFormat('d/m/Y')
                            ->native(false)
                            ->default(now()->toDateString())
                            ->required(),
                        Select::make('type')
                            ->label('Type')
                            ->options(ConsultationType::options())
                            ->default(ConsultationType::FirstVisit->value)
                            ->required(),
                        Select::make('status')
                            ->label('Statut')
                            ->options(ConsultationStatus::options())
                            ->default(ConsultationStatus::Scheduled->value)
                            ->required(),
                        Textarea::make('reason')
                            ->label('Motif')
                            ->rows(3)
                            ->columnSpanFull(),
                        Textarea::make('symptoms')
                            ->label('Symptômes')
                            ->rows(4)
                            ->columnSpanFull(),
                        RichEditor::make('clinical_examination')
                            ->label('Observations cliniques')
                            ->columnSpanFull(),
                    ])
                    ->columns(3),
            ]);
    }

    private static function vitalSignsTab(): Tab
    {
        return Tab::make('Constantes vitales')
            ->icon('heroicon-o-heart')
            ->schema([
                Section::make('Constantes vitales')
                    ->description('L\'IMC est calculé automatiquement à partir du poids et de la taille.')
                    ->schema([
                        Repeater::make('vitalSign')
                            ->label('Constantes')
                            ->relationship('vitalSign')
                            ->defaultItems(1)
                            ->maxItems(1)
                            ->schema([
                                TextInput::make('temperature')
                                    ->label('Température')
                                    ->numeric()
                                    ->step(0.1)
                                    ->suffix('°C'),
                                TextInput::make('weight')
                                    ->label('Poids')
                                    ->numeric()
                                    ->step(0.1)
                                    ->suffix('kg'),
                                TextInput::make('height')
                                    ->label('Taille')
                                    ->numeric()
                                    ->step(0.1)
                                    ->suffix('cm'),
                                Placeholder::make('bmi')
                                    ->label('IMC (kg/m²)')
                                    ->content(fn (Get $get): string => self::formatBmi($get)),
                                TextInput::make('blood_pressure')
                                    ->label('Tension artérielle')
                                    ->placeholder('120/80'),
                                TextInput::make('heart_rate')
                                    ->label('Fréquence cardiaque')
                                    ->numeric()
                                    ->suffix('bpm'),
                                TextInput::make('oxygen_saturation')
                                    ->label('Saturation O₂')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->suffix('%'),
                                TextInput::make('respiratory_rate')
                                    ->label('Fréquence respiratoire')
                                    ->numeric()
                                    ->suffix('/min'),
                            ])
                            ->columns(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    private static function diagnosisTab(): Tab
    {
        return Tab::make('Diagnostic')
            ->icon('heroicon-o-document-text')
            ->schema([
                Section::make('Diagnostic')
                    ->schema([
                        RichEditor::make('diagnosis')
                            ->label('Diagnostic principal')
                            ->columnSpanFull(),
                        Textarea::make('secondary_diagnoses')
                            ->label('Diagnostics secondaires')
                            ->rows(3)
                            ->placeholder('Un diagnostic par ligne')
                            ->columnSpanFull(),
                        RichEditor::make('medical_notes')
                            ->label('Notes médicales')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    private static function treatmentTab(): Tab
    {
        return Tab::make('Traitement')
            ->icon('heroicon-o-beaker')
            ->schema([
                Section::make('Traitement et suivi')
                    ->schema([
                        RichEditor::make('treatment_plan')
                            ->label('Plan thérapeutique')
                            ->columnSpanFull(),
                        RichEditor::make('recommendations')
                            ->label('Recommandations')
                            ->columnSpanFull(),
                        DatePicker::make('follow_up_date')
                            ->label('Date du prochain contrôle')
                            ->displayFormat('d/m/Y')
                            ->native(false)
                            ->nullable(),
                    ])
                    ->columns(2),
            ]);
    }

    private static function documentsTab(): Tab
    {
        return Tab::make('Documents')
            ->icon('heroicon-o-paper-clip')
            ->schema([
                Section::make('Documents médicaux')
                    ->description('Comptes rendus, analyses, imagerie…')
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('consultation_documents')
                            ->label('Documents de la consultation')
                            ->collection('consultation_documents')
                            ->multiple()
                            ->disk('public')
                            ->columnSpanFull(),
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
                    ->orWhere('cin', 'like', "%{$search}%");
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
        return Doctor::query()
            ->when($search, function ($query, string $search): void {
                $query->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('doctor_code', 'like', "%{$search}%");
            })
            ->limit(20)
            ->get()
            ->mapWithKeys(fn (Doctor $doctor): array => [
                $doctor->id => $doctor->full_name,
            ])
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private static function searchAppointments(?string $search): array
    {
        return Appointment::query()
            ->with('patient')
            ->when($search, function ($query, string $search): void {
                $query->where('appointment_number', 'like', "%{$search}%")
                    ->orWhereHas('patient', function ($sub) use ($search): void {
                        $sub->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            })
            ->limit(20)
            ->get()
            ->mapWithKeys(fn (Appointment $appointment): array => [
                $appointment->id => $appointment->appointment_number.' — '.$appointment->patient?->full_name,
            ])
            ->all();
    }

    private static function defaultDoctorId(): ?int
    {
        if (! auth()->user()?->hasRole('doctor')) {
            return null;
        }

        return Doctor::query()->where('user_id', Auth::id())->value('id');
    }

    private static function patientName(Get $get): string
    {
        $patient = self::resolvePatient($get);

        return $patient ? $patient->full_name.' — '.$patient->patient_number : '—';
    }

    private static function patientAge(Get $get): string
    {
        $patient = self::resolvePatient($get);

        return $patient?->age ? $patient->age.' ans' : '—';
    }

    private static function patientGender(Get $get): string
    {
        $patient = self::resolvePatient($get);

        return $patient?->gender?->label() ?? '—';
    }

    private static function patientBloodGroup(Get $get): string
    {
        $patient = self::resolvePatient($get);

        return $patient?->blood_group?->getLabel() ?? '—';
    }

    private static function patientAllergies(Get $get): string
    {
        $patient = self::resolvePatient($get);

        return $patient?->allergies ?: 'Aucune allergie connue';
    }

    private static function patientHistory(Get $get): string
    {
        $patient = self::resolvePatient($get);

        return $patient?->medical_history ?: 'Aucun antécédent renseigné';
    }

    private static function patientChronicDiseases(Get $get): string
    {
        $patient = self::resolvePatient($get);

        return $patient?->chronic_diseases ?: 'Aucune maladie chronique';
    }

    private static function resolvePatient(Get $get): ?Patient
    {
        $patientId = (int) $get('patient_id');

        if (! $patientId) {
            return null;
        }

        if (! isset(self::$patientCache[$patientId])) {
            self::$patientCache[$patientId] = Patient::find($patientId);
        }

        return self::$patientCache[$patientId] ?? null;
    }

    private static function formatBmi(Get $get): string
    {
        $weight = (float) $get('weight');
        $height = (float) $get('height');

        $bmi = \App\Models\VitalSign::computeBmi($weight, $height);

        return $bmi !== null ? number_format($bmi, 1) : '—';
    }
}
