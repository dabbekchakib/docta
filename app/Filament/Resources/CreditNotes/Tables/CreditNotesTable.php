<?php

namespace App\Filament\Resources\CreditNotes\Tables;

use App\Enums\CreditNoteStatus;
use App\Filament\Resources\CreditNotes\Actions\CancelCreditNoteAction;
use App\Filament\Resources\CreditNotes\Actions\DownloadCreditNoteAction;
use App\Filament\Resources\CreditNotes\Actions\IssueCreditNoteAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CreditNotesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('credit_note_number')
                    ->label('N° avoir')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->color('primary'),
                TextColumn::make('invoice.invoice_number')
                    ->label('Facture')
                    ->searchable(),
                TextColumn::make('patient.full_name')
                    ->label('Patient')
                    ->searchable(['first_name', 'last_name', 'patient_number']),
                TextColumn::make('credit_note_date')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('Montant')
                    ->formatStateUsing(fn ($record): string => number_format((float) $record->amount, 3, ',', ' ').' DT')
                    ->sortable()
                    ->alignEnd()
                    ->weight('semibold'),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(CreditNoteStatus::options()),
                SelectFilter::make('patient_id')
                    ->label('Patient')
                    ->relationship('patient', 'full_name')
                    ->searchable(),
            ])
            ->recordActions([
                ViewAction::make(),
                IssueCreditNoteAction::make(),
                DownloadCreditNoteAction::make(),
                CancelCreditNoteAction::make(),
            ])
            ->defaultSort('credit_note_date', 'desc');
    }
}
