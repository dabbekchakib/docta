<?php

namespace App\Filament\Patient\Pages;

use App\Enums\AppointmentStatus;
use App\Enums\AppointmentType;
use App\Filament\Patient\Pages\Concerns\HasPatient;
use App\Models\Appointment;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MyAppointments extends Page implements HasTable
{
    use HasPatient, InteractsWithTable;

    protected string $view = 'filament.patient.pages.my-appointments';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-m-calendar-days';

    protected static string|\UnitEnum|null $navigationGroup = 'Mes soins';

    protected static ?int $navigationSort = 2;

    public function getHeading(): string
    {
        return 'Mes rendez-vous';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                fn (): Builder => Appointment::query()
                    ->where('patient_id', $this->getPatient()?->id)
                    ->with('doctor')
                    ->latest('appointment_date')
            )
            ->columns([
                TextColumn::make('appointment_number')
                    ->label('Numéro')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('appointment_date')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('start_time')
                    ->label('Heure de début')
                    ->formatStateUsing(fn (string $state): string => substr($state, 0, 5))
                    ->sortable(),

                TextColumn::make('end_time')
                    ->label('Heure de fin')
                    ->formatStateUsing(fn (string $state): string => substr($state, 0, 5))
                    ->sortable(),

                TextColumn::make('doctor.full_name')
                    ->label('Médecin')
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query
                        ->whereHas('doctor', fn (Builder $q, string $s): Builder => $q
                            ->where('first_name', 'like', "%{$s}%")
                            ->orWhere('last_name', 'like', "%{$s}%")
                        , $search))
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (AppointmentType $state): string => $state->getLabel())
                    ->color(fn (AppointmentType $state): string|array|null => $state->getColor()),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (AppointmentStatus $state): string => $state->getLabel())
                    ->color(fn (AppointmentStatus $state): string|array|null => $state->getColor()),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(AppointmentStatus::options())
                    ->multiple(),
            ])
            ->defaultSort('appointment_date', 'desc')
            ->emptyStateHeading('Aucun rendez-vous')
            ->emptyStateDescription('Vous n\'avez pas encore de rendez-vous.')
            ->paginated([10, 25, 50]);
    }
}
