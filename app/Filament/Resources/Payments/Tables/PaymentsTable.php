<?php

namespace App\Filament\Resources\Payments\Tables;

use App\Enums\PaymentStatus;
use App\Filament\Resources\Payments\Actions\CancelPaymentAction;
use App\Filament\Resources\Payments\Actions\DownloadPaymentReceiptAction;
use App\Filament\Resources\Payments\Actions\RequestRefundAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('payment_number')
                    ->label('N° paiement')
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
                TextColumn::make('payment_method.name')
                    ->label('Moyen de paiement')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('payment_date')
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
                    ->options(PaymentStatus::options()),
                SelectFilter::make('payment_method_id')
                    ->label('Moyen de paiement')
                    ->relationship('paymentMethod', 'name'),
                SelectFilter::make('patient_id')
                    ->label('Patient')
                    ->relationship('patient', 'full_name')
                    ->searchable(),
            ])
            ->recordActions([
                ViewAction::make(),
                DownloadPaymentReceiptAction::make(),
                RequestRefundAction::make(),
                CancelPaymentAction::make(),
            ])
            ->defaultSort('payment_date', 'desc');
    }
}
