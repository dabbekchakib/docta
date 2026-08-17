<?php

namespace App\Filament\Resources\Invoices\Schemas;

use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\LaboratoryRequest;
use App\Models\Patient;
use App\Models\Service;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informations générales')
                    ->description('Patient, médecin et références de l\'acte facturé.')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('invoice_number')
                            ->label('N° facture')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Généré automatiquement (FAC-2026-000001)'),
                        DatePicker::make('invoice_date')
                            ->label('Date de facturation')
                            ->displayFormat('d/m/Y')
                            ->native(false)
                            ->default(now()->toDateString())
                            ->required(),
                        DatePicker::make('due_date')
                            ->label('Échéance')
                            ->displayFormat('d/m/Y')
                            ->native(false)
                            ->helperText('Laissé vide : paiement à réception.'),
                        Select::make('patient_id')
                            ->label('Patient')
                            ->relationship('patient', 'full_name')
                            ->getSearchResultsUsing(fn (Select $component, ?string $search): array => self::searchPatients($search))
                            ->getOptionLabelFromRecordUsing(fn (Patient $record): string => $record->patient_number.' — '.$record->full_name)
                            ->searchable()
                            ->preload()
                            ->live()
                            ->required(),
                        Select::make('doctor_id')
                            ->label('Médecin')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->default(fn (): ?int => self::defaultDoctorId())
                            ->live()
                            ->options(fn (Select $component): array => self::getFilteredDoctors(null, $component))
                            ->getSearchResultsUsing(fn (Select $component, ?string $search): array => self::getFilteredDoctors($search, $component))
                            ->getOptionLabelFromRecordUsing(fn (Doctor $record): string => $record->full_name.' — '.$record->speciality?->getLabel()),
                        Select::make('consultation_id')
                            ->label('Consultation liée')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->live()
                            ->options(fn (Select $component): array => self::getFilteredConsultations(null, $component))
                            ->getSearchResultsUsing(fn (Select $component, ?string $search): array => self::getFilteredConsultations($search, $component))
                            ->getOptionLabelFromRecordUsing(fn (Consultation $record): string => $record->consultation_number.' — '.$record->consultation_date?->format('d/m/Y')),
                        Select::make('appointment_id')
                            ->label('Rendez-vous lié')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->live()
                            ->options(fn (Select $component): array => self::getFilteredAppointments(null, $component))
                            ->getSearchResultsUsing(fn (Select $component, ?string $search): array => self::getFilteredAppointments($search, $component))
                            ->getOptionLabelFromRecordUsing(fn (Appointment $record): string => $record->appointment_number.' — '.$record->appointment_date?->format('d/m/Y')),
                        Select::make('laboratory_request_id')
                            ->label('Demande d\'examens liée')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->live()
                            ->options(fn (Select $component): array => self::getFilteredLaboratoryRequests(null, $component))
                            ->getSearchResultsUsing(fn (Select $component, ?string $search): array => self::getFilteredLaboratoryRequests($search, $component))
                            ->getOptionLabelFromRecordUsing(fn (LaboratoryRequest $record): string => $record->request_number.' — '.$record->patient?->full_name),
                    ])
                    ->columns(3),
                Section::make('Lignes de facturation')
                    ->description('Les montants sont recalculés côté serveur : sous-total, remise, TVA et total.')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('items')
                            ->label('Prestations facturées')
                            ->columnSpanFull()
                            ->defaultItems(1)
                            ->minItems(1)
                            ->collapsible()
                            ->reorderableWithButtons()
                            ->addActionLabel('Ajouter une prestation')
                            ->afterStateHydrated(function (Repeater $component, ?Model $record): void {
                                if ($record && method_exists($record, 'items')) {
                                    $component->state($record->items->sortBy('sort_order')->values()->toArray());
                                }
                            })
                            ->schema([
                                Select::make('service_id')
                                    ->label('Prestation')
                                    ->options(fn (): array => self::searchServices(null))
                                    ->getSearchResultsUsing(fn (Select $component, ?string $search): array => self::searchServices($search))
                                    ->getOptionLabelFromRecordUsing(fn (Service $record): string => $record->name.' — '.$record->priceLabel())
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function ($state, $set): void {
                                        $service = Service::withTrashed()->find((int) $state);

                                        if (! $service) {
                                            return;
                                        }

                                        $set('description', $service->name);
                                        $set('unit_price', (string) $service->price);
                                        $set('tax_rate', (string) ($service->taxRate?->rate ?? '0'));
                                    }),
                                TextInput::make('description')
                                    ->label('Désignation')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('quantity')
                                    ->label('Quantité')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0.001)
                                    ->default(1)
                                    ->step('0.001'),
                                TextInput::make('unit_price')
                                    ->label('Prix unitaire (TND)')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0)
                                    ->step('0.001')
                                    ->prefix('DT'),
                                TextInput::make('discount_percent')
                                    ->label('Remise (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->default(0)
                                    ->step('0.01'),
                                TextInput::make('tax_rate')
                                    ->label('TVA (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step('0.01')
                                    ->helperText('Appliquée automatiquement depuis le tarif.'),
                            ])
                            ->columns(3),
                    ]),
                Section::make('Remise et notes')
                    ->columnSpanFull()
                    ->schema([
                        Select::make('discount_type')
                            ->label('Type de remise')
                            ->options([
                                'none' => 'Aucune remise',
                                'percent' => 'Pourcentage',
                                'amount' => 'Montant fixe',
                            ])
                            ->default('none')
                            ->live()
                            ->native(false),
                        TextInput::make('discount_value')
                            ->label('Valeur de la remise')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->step('0.001')
                            ->prefix(fn ($state): string => 'DT')
                            ->visible(fn ($state): bool => $state !== null),
                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    private static function formValue(Select $component, string $key): mixed
    {
        return data_get($component->getLivewire(), "data.{$key}");
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
     * Médecins filtrés par les consultations du patient sélectionné.
     *
     * @return array<int, string>
     */
    private static function getFilteredDoctors(?string $search, Select $component): array
    {
        $patientId = (int) self::formValue($component, 'patient_id');

        $query = Doctor::query()->where('status', 'active');

        if ($patientId > 0) {
            $query->whereHas('consultations', function (Builder $q) use ($patientId): void {
                $q->where('patient_id', $patientId);
            });
        }

        if ($search) {
            $query->where(function (Builder $q) use ($search): void {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('doctor_code', 'like', "%{$search}%");
            });
        }

        return $query->limit(50)
            ->get()
            ->mapWithKeys(fn (Doctor $doctor): array => [
                $doctor->id => $doctor->full_name.' — '.$doctor->speciality?->getLabel(),
            ])
            ->all();
    }

    /**
     * Consultations filtrées par patient et médecin.
     *
     * @return array<int, string>
     */
    private static function getFilteredConsultations(?string $search, Select $component): array
    {
        $patientId = (int) self::formValue($component, 'patient_id');
        $doctorId = (int) self::formValue($component, 'doctor_id');

        $query = Consultation::query()->with('patient');

        if ($patientId > 0) {
            $query->where('patient_id', $patientId);
        }

        if ($doctorId > 0) {
            $query->where('doctor_id', $doctorId);
        }

        if ($search) {
            $query->where(function (Builder $q) use ($search): void {
                $q->where('consultation_number', 'like', "%{$search}%")
                    ->orWhereHas('patient', function (Builder $sub) use ($search): void {
                        $sub->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        }

        return $query->limit(50)
            ->get()
            ->mapWithKeys(fn (Consultation $consultation): array => [
                $consultation->id => $consultation->consultation_number.' — '.$consultation->patient?->full_name,
            ])
            ->all();
    }

    /**
     * Rendez-vous filtrés par patient et médecin.
     *
     * @return array<int, string>
     */
    private static function getFilteredAppointments(?string $search, Select $component): array
    {
        $patientId = (int) self::formValue($component, 'patient_id');
        $doctorId = (int) self::formValue($component, 'doctor_id');

        $query = Appointment::query();

        if ($patientId > 0) {
            $query->where('patient_id', $patientId);
        }

        if ($doctorId > 0) {
            $query->where('doctor_id', $doctorId);
        }

        if ($search) {
            $query->where(function (Builder $q) use ($search): void {
                $q->where('appointment_number', 'like', "%{$search}%");
            });
        }

        return $query->limit(50)
            ->get()
            ->mapWithKeys(fn (Appointment $appointment): array => [
                $appointment->id => $appointment->appointment_number.' — '.$appointment->appointment_date?->format('d/m/Y'),
            ])
            ->all();
    }

    /**
     * Demandes d'examens filtrées par patient et médecin.
     *
     * @return array<int, string>
     */
    private static function getFilteredLaboratoryRequests(?string $search, Select $component): array
    {
        $patientId = (int) self::formValue($component, 'patient_id');
        $doctorId = (int) self::formValue($component, 'doctor_id');

        $query = LaboratoryRequest::query()->with('patient');

        if ($patientId > 0) {
            $query->where('patient_id', $patientId);
        }

        if ($doctorId > 0) {
            $query->where('doctor_id', $doctorId);
        }

        if ($search) {
            $query->where(function (Builder $q) use ($search): void {
                $q->where('request_number', 'like', "%{$search}%")
                    ->orWhereHas('patient', function (Builder $sub) use ($search): void {
                        $sub->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        }

        return $query->limit(50)
            ->get()
            ->mapWithKeys(fn (LaboratoryRequest $request): array => [
                $request->id => $request->request_number.' — '.$request->patient?->full_name,
            ])
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private static function searchServices(?string $search): array
    {
        return Service::query()
            ->where('is_active', true)
            ->when($search, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->limit(20)
            ->get()
            ->mapWithKeys(fn (Service $service): array => [
                $service->id => $service->name.' — '.$service->priceLabel(),
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
}
