<?php

namespace App\Filament\Resources\LaboratoryReports\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LaboratoryReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('report_number')
                    ->label('N° compte rendu')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->color('primary'),
                TextColumn::make('report_date')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('request.request_number')
                    ->label('N° demande')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('request.patient.full_name')
                    ->label('Patient')
                    ->searchable(['first_name', 'last_name', 'patient_number']),
                TextColumn::make('request.doctor.full_name')
                    ->label('Médecin')
                    ->searchable(['first_name', 'last_name'])
                    ->toggleable(),
                TextColumn::make('request.laboratory.display_name')
                    ->label('Laboratoire')
                    ->placeholder('Non désigné')
                    ->toggleable(),
                TextColumn::make('summary')
                    ->label('Synthèse')
                    ->limit(50)
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('validatedBy.name')
                    ->label('Validé par')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('report_date')
                    ->label('Période')
                    ->options([
                        'today' => 'Aujourd\'hui',
                        'week' => 'Cette semaine',
                        'month' => 'Ce mois-ci',
                    ])
                    ->query(function ($query, array $data): void {
                        $value = $data['value'] ?? null;

                        if ($value === 'today') {
                            $query->whereDate('report_date', today());
                        } elseif ($value === 'week') {
                            $query->whereBetween('report_date', [now()->startOfWeek(), now()->endOfWeek()]);
                        } elseif ($value === 'month') {
                            $query->whereBetween('report_date', [now()->startOfMonth(), now()->endOfMonth()]);
                        }
                    }),
                SelectFilter::make('laboratory')
                    ->label('Laboratoire')
                    ->relationship('request.laboratory', 'name')
                    ->searchable(),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
