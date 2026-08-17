<?php

namespace App\Filament\Patient\Widgets;

use App\Models\Patient;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentConsultationsWidget extends TableWidget
{
    protected static ?string $heading = 'Consultations récentes';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => $this->getConsultationsQuery())
            ->columns([
                TextColumn::make('consultation_date')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('doctor.full_name')
                    ->label('Médecin')
                    ->searchable(['first_name', 'last_name']),
                TextColumn::make('type')
                    ->label('Type')
                    ->badge(),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge(),
            ])
            ->paginated(false);
    }

    protected function getConsultationsQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $patient = $this->getPatient();

        if (! $patient) {
            return \App\Models\Consultation::query()->whereRaw('0 = 1');
        }

        return $patient->consultations()
            ->with('doctor')
            ->latest('consultation_date')
            ->limit(5);
    }

    protected function getPatient(): ?Patient
    {
        return auth()->user()->patient;
    }
}
