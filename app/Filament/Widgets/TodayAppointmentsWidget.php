<?php

namespace App\Filament\Widgets;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Doctor;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TodayAppointmentsWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Rendez-vous du jour')
            ->query($this->todayQuery())
            ->columns([
                TextColumn::make('start_time')
                    ->label('Heure')
                    ->sortable()
                    ->weight('semibold'),
                TextColumn::make('patient.full_name')
                    ->label('Patient')
                    ->searchable(['patient.first_name', 'patient.last_name']),
                TextColumn::make('doctor.full_name')
                    ->label('Médecin')
                    ->searchable(['doctor.first_name', 'doctor.last_name']),
                TextColumn::make('type')
                    ->label('Type')
                    ->badge(),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge(),
            ])
            ->paginated(false)
            ->defaultPaginationPageOption(10);
    }

    private function todayQuery()
    {
        $query = Appointment::query()
            ->with(['patient', 'doctor'])
            ->whereDate('appointment_date', today())
            ->orderBy('start_time');

        if (auth()->user()?->hasRole('doctor')) {
            $doctorId = Doctor::query()->where('user_id', auth()->id())->value('id');
            $query->where('doctor_id', $doctorId ?: -1);
        }

        return $query;
    }
}
