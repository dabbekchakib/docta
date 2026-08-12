<?php

namespace App\Filament\Resources\LaboratoryRequests\Tables;

use App\Enums\LaboratoryRequestPriority;
use App\Enums\LaboratoryRequestStatus;
use App\Filament\Resources\LaboratoryRequests\Actions\AcceptLaboratoryRequestAction;
use App\Filament\Resources\LaboratoryRequests\Actions\CancelLaboratoryRequestAction;
use App\Filament\Resources\LaboratoryRequests\Actions\CollectSampleAction;
use App\Filament\Resources\LaboratoryRequests\Actions\EnterResultsAction;
use App\Filament\Resources\LaboratoryRequests\Actions\ReceiveSamplesAction;
use App\Filament\Resources\LaboratoryRequests\Actions\RejectSampleAction;
use App\Filament\Resources\LaboratoryRequests\Actions\SubmitLaboratoryRequestAction;
use App\Filament\Resources\LaboratoryRequests\Actions\ValidateResultsAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LaboratoryRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('request_number')
                    ->label('N° demande')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->color('primary'),
                TextColumn::make('patient.full_name')
                    ->label('Patient')
                    ->searchable(['first_name', 'last_name', 'patient_number']),
                TextColumn::make('doctor.full_name')
                    ->label('Médecin')
                    ->searchable(['first_name', 'last_name'])
                    ->toggleable(),
                TextColumn::make('laboratory.display_name')
                    ->label('Laboratoire')
                    ->placeholder('Non désigné')
                    ->toggleable(),
                TextColumn::make('requested_at')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('priority')
                    ->label('Priorité')
                    ->badge()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(LaboratoryRequestStatus::options()),
                SelectFilter::make('priority')
                    ->label('Priorité')
                    ->options(LaboratoryRequestPriority::options()),
                SelectFilter::make('patient_id')
                    ->label('Patient')
                    ->relationship('patient', 'full_name')
                    ->searchable(),
                SelectFilter::make('doctor_id')
                    ->label('Médecin')
                    ->relationship('doctor', 'full_name')
                    ->searchable(),
                SelectFilter::make('laboratory_id')
                    ->label('Laboratoire')
                    ->relationship('laboratory', 'name')
                    ->searchable(),
            ])
            ->recordActions([
                ViewAction::make(),
                SubmitLaboratoryRequestAction::make(),
                AcceptLaboratoryRequestAction::make(),
                CollectSampleAction::make(),
                ReceiveSamplesAction::make(),
                RejectSampleAction::make(),
                EnterResultsAction::make(),
                ValidateResultsAction::make(),
                CancelLaboratoryRequestAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
