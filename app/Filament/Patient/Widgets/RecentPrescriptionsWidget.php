<?php

namespace App\Filament\Patient\Widgets;

use App\Models\Patient;
use App\Models\Prescription;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentPrescriptionsWidget extends TableWidget
{
    protected static ?string $heading = 'Ordonnances récentes';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => $this->getPrescriptionsQuery())
            ->columns([
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
            ->paginated(false);
    }

    protected function getPrescriptionsQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $patient = $this->getPatient();

        if (! $patient) {
            return \App\Models\Prescription::query()->whereRaw('0 = 1');
        }

        return Prescription::query()
            ->where('patient_id', $patient->id)
            ->with('doctor')
            ->latest('prescription_date')
            ->limit(5);
    }

    protected function getPatient(): ?Patient
    {
        return auth()->user()->patient;
    }
}
