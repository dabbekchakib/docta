<?php

namespace App\Filament\Patient\Pages;

use App\Enums\PrescriptionStatus;
use App\Filament\Patient\Pages\Concerns\HasPatient;
use App\Models\Prescription;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class MyPrescriptions extends Page implements HasTable
{
    use HasPatient;
    use InteractsWithTable;

    protected string $view = 'filament.patient.pages.my-prescriptions';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'Mes ordonnances';

    protected static string|\UnitEnum|null $navigationGroup = 'Mes soins';

    protected static ?int $navigationSort = 2;

    public function getHeading(): string
    {
        return 'Mes ordonnances';
    }

    public function table(Table $table): Table
    {
        $patient = $this->getPatient();

        return $table
            ->query(
                Prescription::query()
                    ->where('patient_id', $patient?->id ?? 0)
                    ->where('status', PrescriptionStatus::Issued)
                    ->with('doctor')
                    ->latest('prescription_date')
            )
            ->columns([
                TextColumn::make('prescription_number')
                    ->label('N° ordonnance')
                    ->searchable()
                    ->sortable()
                    ->url(fn ($record): string => ViewPrescription::getUrl(['prescriptionId' => $record->id])),
                TextColumn::make('prescription_date')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('doctor.full_name')
                    ->label('Médecin')
                    ->searchable(['first_name', 'last_name']),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge(),
            ])
            ->defaultSort('prescription_date', 'desc')
            ->paginated([10, 25, 50]);
    }
}
