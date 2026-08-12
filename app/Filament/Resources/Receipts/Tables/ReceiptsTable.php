<?php

namespace App\Filament\Resources\Receipts\Tables;

use App\Filament\Resources\Receipts\Actions\DownloadReceiptAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ReceiptsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('receipt_number')
                    ->label('N° reçu')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->color('primary'),
                TextColumn::make('patient.full_name')
                    ->label('Patient')
                    ->searchable(['first_name', 'last_name', 'patient_number']),
                TextColumn::make('invoice.invoice_number')
                    ->label('Facture')
                    ->searchable()
                    ->placeholder('—'),
                TextColumn::make('payment.payment_number')
                    ->label('Paiement')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('receipt_date')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('Montant')
                    ->formatStateUsing(fn ($record): string => number_format((float) $record->amount, 3, ',', ' ').' DT')
                    ->sortable()
                    ->alignEnd()
                    ->weight('semibold'),
            ])
            ->filters([
                SelectFilter::make('patient_id')
                    ->label('Patient')
                    ->relationship('patient', 'full_name')
                    ->searchable(),
            ])
            ->recordActions([
                ViewAction::make(),
                DownloadReceiptAction::make(),
            ])
            ->defaultSort('receipt_date', 'desc');
    }
}
