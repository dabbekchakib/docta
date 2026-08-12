<?php

namespace App\Filament\Resources\LaboratoryRequests\Schemas;

use App\Enums\LaboratoryRequestPriority;
use App\Enums\SampleType;
use App\Models\Doctor;
use App\Models\Laboratory;
use App\Models\LaboratoryTest;
use App\Models\Patient;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class LaboratoryRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Patient')
                    ->description('Les informations du patient sont en lecture seule lorsqu\'elles proviennent d\'une consultation.')
                    ->columnSpanFull()
                    ->schema([
                        Select::make('patient_id')
                            ->label('Patient')
                            ->relationship('patient', 'full_name')
                            ->getSearchResultsUsing(fn (Select $component, ?string $search): array => self::searchPatients($search))
                            ->getOptionLabelFromRecordUsing(fn (Patient $record): string => $record->patient_number.' — '.$record->full_name)
                            ->searchable()
                            ->preload()
                            ->disabled(fn (): bool => self::patientLocked())
                            ->live()
                            ->afterStateUpdated(fn (Set $set, $state): ?string => self::fillPatientInfo($set, $state))
                            ->required(),
                        TextInput::make('patient_birth_date')
                            ->label('Date de naissance')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('patient_gender')
                            ->label('Sexe')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('patient_number')
                            ->label('N° patient')
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->columns(4),
                Section::make('Médecin prescripteur')
                    ->columnSpanFull()
                    ->schema([
                        Select::make('doctor_id')
                            ->label('Médecin')
                            ->relationship('doctor', 'full_name')
                            ->getSearchResultsUsing(fn (Select $component, ?string $search): array => self::searchDoctors($search))
                            ->getOptionLabelFromRecordUsing(fn (Doctor $record): string => $record->full_name.' — '.$record->speciality?->getLabel())
                            ->searchable()
                            ->preload()
                            ->disabled(fn (): bool => self::doctorLocked())
                            ->default(fn (): ?int => self::defaultDoctorId())
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
                            ->searchable()
                            ->preload()
                            ->disabled(fn (): bool => self::consultationLocked())
                            ->nullable(),
                    ])
                    ->columns(2),
                Section::make('Laboratoire')
                    ->columnSpanFull()
                    ->schema([
                        Select::make('laboratory_id')
                            ->label('Laboratoire destinataire')
                            ->options(fn (): array => Laboratory::query()
                                ->where('is_active', true)
                                ->orderBy('name')
                                ->get()
                                ->mapWithKeys(fn (Laboratory $laboratory): array => [$laboratory->id => $laboratory->display_name])
                                ->all())
                            ->searchable()
                            ->preload()
                            ->helperText('Laisser vide si aucun laboratoire n\'est encore désigné.')
                            ->nullable(),
                    ]),
                Section::make('Demande')
                    ->columnSpanFull()
                    ->schema([
                        DatePicker::make('requested_at')
                            ->label('Date de la demande')
                            ->displayFormat('d/m/Y')
                            ->native(false)
                            ->default(now()->toDateString())
                            ->required(),
                        Select::make('priority')
                            ->label('Priorité')
                            ->options(LaboratoryRequestPriority::options())
                            ->default(LaboratoryRequestPriority::Normal->value)
                            ->native(false)
                            ->required(),
                        Textarea::make('clinical_information')
                            ->label('Informations cliniques')
                            ->rows(3)
                            ->columnSpanFull()
                            ->helperText('Motif clinique justifiant la demande d\'examens.'),
                        Textarea::make('doctor_notes')
                            ->label('Notes du médecin')
                            ->rows(2)
                            ->columnSpanFull(),
                        Textarea::make('patient_instructions')
                            ->label('Instructions pour le patient')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Examens demandés')
                    ->description('Sélectionnez un ou plusieurs examens. Le type de prélèvement est pré-rempli automatiquement.')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('items')
                            ->label('Examens')
                            ->relationship('items')
                            ->orderColumn('sort_order')
                            ->defaultItems(1)
                            ->minItems(1)
                            ->collapsible()
                            ->reorderableWithButtons()
                            ->addActionLabel('Ajouter un examen')
                            ->schema([
                                Select::make('laboratory_test_id')
                                    ->label('Examen')
                                    ->relationship('test', 'name', function (Builder $query): Builder {
                                        return $query->where('is_active', true)->orderBy('name');
                                    })
                                    ->getSearchResultsUsing(fn (Select $component, ?string $search): array => self::searchTests($search))
                                    ->getOptionLabelFromRecordUsing(fn (LaboratoryTest $record): string => $record->name.' — '.($record->category?->name ?? 'Sans catégorie'))
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, $state): void {
                                        $test = $state ? LaboratoryTest::query()->find($state) : null;

                                        if ($test) {
                                            $set('sample_type', $test->sample_type->value);
                                            $set('instructions', $test->instructions);
                                        }
                                    })
                                    ->required(),
                                Select::make('sample_type')
                                    ->label('Type de prélèvement')
                                    ->options(SampleType::options())
                                    ->default(SampleType::Blood->value)
                                    ->native(false),
                                Textarea::make('instructions')
                                    ->label('Instructions')
                                    ->rows(2),
                                Textarea::make('notes')
                                    ->label('Notes')
                                    ->rows(2),
                            ])
                            ->columns(2),
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

    private static function defaultDoctorId(): ?int
    {
        if (! auth()->user()?->hasRole('doctor')) {
            return null;
        }

        return Doctor::query()->where('user_id', Auth::id())->value('id');
    }

    private static function fillPatientInfo(Set $set, mixed $state): ?string
    {
        $patient = $state ? Patient::query()->find($state) : null;

        $set('patient_birth_date', $patient?->birth_date?->format('d/m/Y') ?? null);
        $set('patient_gender', $patient?->gender?->getLabel() ?? null);
        $set('patient_number', $patient?->patient_number ?? null);

        return null;
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
    private static function searchTests(?string $search): array
    {
        return LaboratoryTest::query()
            ->where('is_active', true)
            ->with('category')
            ->when($search, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->limit(20)
            ->get()
            ->mapWithKeys(fn (LaboratoryTest $test): array => [
                $test->id => $test->name.' — '.($test->category?->name ?? 'Sans catégorie'),
            ])
            ->all();
    }
}
