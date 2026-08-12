<?php

namespace App\Filament\Resources\Refunds\Tables;

use App\Enums\RefundStatus;
use App\Filament\Resources\Refunds\Actions\ApproveRefundAction;
use App\Filament\Resources\Refunds\Actions\CancelRefundAction;
use App\Filament\Resources\Refunds\Actions\ExecuteRefundAction;
use App\Filament\Resources\Refunds\Actions\RejectRefundAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RefundsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('refund_number')
                    ->label('N° remboursement')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->color('primary'),
                TextColumn::make('payment.payment_number')
                    ->label('Paiement')
                    ->searchable()
                    ->placeholder('—'),
                TextColumn::make('patient.full_name')
                    ->label('Patient')
                    ->searchable(['first_name', 'last_name', 'patient_number']),
                TextColumn::make('refund_date')
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
                    ->options(RefundStatus::options()),
                SelectFilter::make('patient_id')
                    ->label('Patient')
                    ->relationship('patient', 'full_name')
                    ->searchable(),
            ])
            ->recordActions([
                ViewAction::make(),
                ApproveRefundAction::make(),
                ExecuteRefundAction::make(),
                RejectRefundAction::make(),
                CancelRefundAction::make(),
            ])
            ->defaultSort('refund_date', 'desc');
    }
}
