<?php

namespace App\Filament\Resources\Invoices\Tables;

use App\Enums\InvoiceStatus;
use App\Filament\Resources\Invoices\Actions\CancelInvoiceAction;
use App\Filament\Resources\Invoices\Actions\DownloadInvoiceAction;
use App\Filament\Resources\Invoices\Actions\IssueInvoiceAction;
use App\Filament\Resources\Invoices\Actions\RecordPaymentAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class InvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_number')
                    ->label('N° facture')
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
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('invoice_date')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('total')
                    ->label('Total')
                    ->formatStateUsing(fn ($record): string => number_format((float) $record->total, 3, ',', ' ').' DT')
                    ->sortable()
                    ->alignEnd()
                    ->weight('semibold'),
                TextColumn::make('amount_paid')
                    ->label('Encaissé')
                    ->formatStateUsing(fn ($record): string => number_format((float) $record->amount_paid, 3, ',', ' ').' DT')
                    ->alignEnd()
                    ->toggleable(),
                TextColumn::make('amount_remaining')
                    ->label('Restant dû')
                    ->formatStateUsing(fn ($record): string => number_format((float) $record->amount_remaining, 3, ',', ' ').' DT')
                    ->color(fn ($record): string => (float) $record->amount_remaining > 0 ? 'danger' : 'success')
                    ->alignEnd()
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(InvoiceStatus::options()),
                SelectFilter::make('patient_id')
                    ->label('Patient')
                    ->relationship('patient', 'full_name')
                    ->searchable(),
                SelectFilter::make('doctor_id')
                    ->label('Médecin')
                    ->relationship('doctor', 'full_name')
                    ->searchable(),
                TernaryFilter::make('overdue')
                    ->label('En retard')
                    ->queries(
                        true: fn ($query) => $query->whereIn('status', [InvoiceStatus::Issued, InvoiceStatus::PartiallyPaid, InvoiceStatus::Overdue])->whereDate('due_date', '<', now()->toDateString()),
                        false: fn ($query) => $query->whereNotIn('status', [InvoiceStatus::Overdue]),
                    ),
            ])
            ->recordActions([
                ViewAction::make(),
                IssueInvoiceAction::make(),
                RecordPaymentAction::make(),
                DownloadInvoiceAction::make(),
                CancelInvoiceAction::make(),
            ])
            ->defaultSort('invoice_date', 'desc');
    }
}
