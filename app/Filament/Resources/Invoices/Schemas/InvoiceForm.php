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
                            ->relationship('doctor', 'full_name')
                            ->getSearchResultsUsing(fn (Select $component, ?string $search): array => self::searchDoctors($search))
                            ->getOptionLabelFromRecordUsing(fn (Doctor $record): string => $record->full_name.' — '.$record->speciality?->getLabel())
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->default(fn (): ?int => self::defaultDoctorId()),
                        Select::make('consultation_id')
                            ->label('Consultation liée')
                            ->relationship('consultation', 'consultation_number')
                            ->getSearchResultsUsing(function (Select $component, ?string $search): array {
                                $patientId = (int) $component->getState();

                                return self::searchConsultations($search, $patientId);
                            })
                            ->getOptionLabelFromRecordUsing(fn (Consultation $record): string => $record->consultation_number.' — '.$record->consultation_date?->format('d/m/Y'))
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Select::make('appointment_id')
                            ->label('Rendez-vous lié')
                            ->relationship('appointment', 'appointment_number')
                            ->getOptionLabelFromRecordUsing(fn (Appointment $record): string => $record->appointment_number.' — '.$record->appointment_date?->format('d/m/Y'))
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Select::make('laboratory_request_id')
                            ->label('Demande d\'examens liée')
                            ->relationship('laboratoryRequest', 'request_number')
                            ->getSearchResultsUsing(fn (Select $component, ?string $search): array => self::searchLaboratoryRequests($search))
                            ->getOptionLabelFromRecordUsing(fn (LaboratoryRequest $record): string => $record->request_number.' — '.$record->patient?->full_name)
                            ->searchable()
                            ->preload()
                            ->nullable(),
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
                            ->schema([
                                Select::make('service_id')
                                    ->label('Prestation')
                                    ->relationship('service', 'name')
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

    /**
     * @return array<int, string>
     */
    private static function searchConsultations(?string $search, int $patientId): array
    {
        return Consultation::query()
            ->with('patient')
            ->when($patientId > 0, fn ($query) => $query->where('patient_id', $patientId))
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
                $consultation->id => $consultation->consultation_number.' — '.$consultation->patient?->full_name,
            ])
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private static function searchLaboratoryRequests(?string $search): array
    {
        return LaboratoryRequest::query()
            ->with('patient')
            ->when($search, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('request_number', 'like', "%{$search}%")
                        ->orWhereHas('patient', function ($sub) use ($search): void {
                            $sub->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        });
                });
            })
            ->limit(20)
            ->get()
            ->mapWithKeys(fn (LaboratoryRequest $request): array => [
                $request->id => $request->request_number.' — '.$request->patient?->full_name,
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
