<?php

namespace App\Filament\Resources\Prescriptions\Schemas;

use App\Enums\DurationUnit;
use App\Enums\MedicineForm;
use App\Enums\MedicineRoute;
use App\Enums\PrescriptionStatus;
use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\Patient;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PrescriptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informations générales')
                    ->description('Patient, médecin prescripteur et période de validité.')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('prescription_number')
                            ->label('N° ordonnance')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Généré automatiquement'),
                        DatePicker::make('prescription_date')
                            ->label('Date de l\'ordonnance')
                            ->displayFormat('d/m/Y')
                            ->native(false)
                            ->default(now()->toDateString())
                            ->required(),
                        Select::make('status')
                            ->label('Statut')
                            ->options(PrescriptionStatus::options())
                            ->default(PrescriptionStatus::Draft->value)
                            ->disabled()
                            ->dehydrated(false),
                        Select::make('patient_id')
                            ->label('Patient')
                            ->relationship('patient', 'full_name')
                            ->getSearchResultsUsing(fn (Select $component, ?string $search): array => self::searchPatients($search))
                            ->getOptionLabelFromRecordUsing(fn (Patient $record): string => $record->patient_number.' — '.$record->full_name)
                            ->searchable()
                            ->preload()
                            ->disabled(fn (): bool => self::patientLocked())
                            ->live()
                            ->required(),
                        Select::make('doctor_id')
                            ->label('Médecin')
                            ->relationship('doctor', 'full_name')
                            ->getSearchResultsUsing(fn (Select $component, ?string $search): array => self::searchDoctors($search))
                            ->getOptionLabelFromRecordUsing(fn (Doctor $record): string => $record->full_name.' — '.$record->speciality?->getLabel())
                            ->searchable()
                            ->preload()
                            ->disabled(fn (): bool => self::doctorLocked())
                            ->default(fn (): ?int => self::defaultDoctorId())
                            ->live()
                            ->required(),
                        Select::make('consultation_id')
                            ->label('Consultation liée')
                            ->relationship('consultation', 'consultation_number', function (Builder $query, Get $get): Builder {
                                $patientId = (int) $get('patient_id');
                                $doctorId = (int) $get('doctor_id');

                                return $query
                                    ->when($patientId > 0, fn (Builder $query): Builder => $query->where('patient_id', $patientId))
                                    ->when($doctorId > 0, fn (Builder $query): Builder => $query->where('doctor_id', $doctorId));
                            })
                            ->getSearchResultsUsing(function (Select $component, ?string $search): array {
                                $patientId = $component->evaluate(fn (Get $get): int => (int) $get('patient_id'));
                                $doctorId = $component->evaluate(fn (Get $get): int => (int) $get('doctor_id'));

                                return self::searchConsultations($search, $patientId, $doctorId);
                            })
                            ->getOptionLabelFromRecordUsing(fn (Consultation $record): string => $record->consultation_number.' — '.$record->consultation_date?->format('d/m/Y'))
                            ->searchable()
                            ->preload()
                            ->disabled(fn (): bool => self::consultationLocked())
                            ->nullable(),
                        DatePicker::make('valid_until')
                            ->label('Valable jusqu\'au')
                            ->displayFormat('d/m/Y')
                            ->native(false)
                            ->nullable(),
                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(3),
                View::make('filament.infolists.dmp-alerts')
                    ->columnSpanFull()
                    ->viewData(fn (Get $get): array => ['medicalRecord' => self::resolveMedicalRecord($get)]),
                Section::make('Médicaments prescrits')
                    ->description('Dosage, fréquence et durée de chaque traitement.')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('items')
                            ->label('Médicaments')
                            ->relationship('items')
                            ->orderColumn('sort_order')
                            ->defaultItems(1)
                            ->minItems(1)
                            ->collapsible()
                            ->reorderableWithButtons()
                            ->addActionLabel('Ajouter un médicament')
                            ->schema([
                                TextInput::make('medicine_name')
                                    ->label('Médicament')
                                    ->required(),
                                TextInput::make('active_ingredient')
                                    ->label('Principe actif'),
                                TextInput::make('dosage')
                                    ->label('Dosage')
                                    ->required()
                                    ->placeholder('ex. 500 mg'),
                                Select::make('form')
                                    ->label('Forme')
                                    ->options(MedicineForm::options())
                                    ->default(MedicineForm::Tablet->value)
                                    ->native(false),
                                Select::make('route')
                                    ->label('Voie d\'administration')
                                    ->options(MedicineRoute::options())
                                    ->default(MedicineRoute::Oral->value)
                                    ->native(false),
                                TextInput::make('frequency')
                                    ->label('Fréquence')
                                    ->required()
                                    ->placeholder('ex. 2 fois par jour'),
                                TextInput::make('duration')
                                    ->label('Durée')
                                    ->required(),
                                Select::make('duration_unit')
                                    ->label('Unité')
                                    ->options(DurationUnit::options())
                                    ->default(DurationUnit::Day->value)
                                    ->native(false),
                                TextInput::make('quantity')
                                    ->label('Quantité')
                                    ->numeric()
                                    ->minValue(0),
                                Textarea::make('instructions')
                                    ->label('Instructions')
                                    ->rows(2),
                                Textarea::make('notes')
                                    ->label('Notes')
                                    ->rows(2),
                            ])
                            ->columns(4),
                    ]),
            ]);
    }

    /**
     * Le patient est verrouillé lorsqu'une consultation est présélectionnée.
     */
    private static function patientLocked(): bool
    {
        if ((bool) auth()->user()?->hasRole('doctor')) {
            return false;
        }

        return (bool) request()->query('consultation');
    }

    /**
     * Le médecin est verrouillé pour un médecin connecté ou via une consultation.
     */
    private static function doctorLocked(): bool
    {
        if ((bool) auth()->user()?->hasRole('doctor')) {
            return true;
        }

        return (bool) request()->query('consultation');
    }

    private static function consultationLocked(): bool
    {
        return (bool) request()->query('consultation');
    }

    /**
     * @return array<int, string>
     */
    private static function searchPatients(?string $search): array
    {
        return Patient::query()
            ->when($search, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('patient_number', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('cin', 'like', "%{$search}%");
                });
            })
            ->limit(20)
            ->get()
            ->mapWithKeys(fn (Patient $patient): array => [
                $patient->id => $patient->patient_number.' — '.$patient->full_name,
            ])
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private static function searchDoctors(?string $search): array
    {
        return Doctor::query()
            ->where('status', 'active')
            ->when($search, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('doctor_code', 'like', "%{$search}%");
                });
            })
            ->limit(20)
            ->get()
            ->mapWithKeys(fn (Doctor $doctor): array => [
                $doctor->id => $doctor->full_name.' — '.$doctor->speciality?->getLabel(),
            ])
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private static function searchConsultations(?string $search, int $patientId, int $doctorId): array
    {
        return Consultation::query()
            ->with('patient')
            ->when($patientId > 0, fn ($query) => $query->where('patient_id', $patientId))
            ->when($doctorId > 0, fn ($query) => $query->where('doctor_id', $doctorId))
            ->when($search, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('consultation_number', 'like', "%{$search}%")
                        ->orWhereHas('patient', function ($sub) use ($search): void {
                            $sub->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        });
                });
            })
            ->limit(20)
            ->get()
            ->mapWithKeys(fn (Consultation $consultation): array => [
                $consultation->id => $consultation->consultation_number.' — '.$consultation->patient?->full_name.' — '.$consultation->consultation_date?->format('d/m/Y'),
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

    private static function resolveMedicalRecord(Get $get): mixed
    {
        $patientId = (int) $get('patient_id');

        if (! $patientId) {
            return null;
        }

        return Patient::query()->find($patientId)?->medicalRecord;
    }
}
