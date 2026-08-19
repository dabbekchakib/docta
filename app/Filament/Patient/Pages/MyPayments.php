<?php

namespace App\Filament\Patient\Pages;

use App\Filament\Patient\Pages\Concerns\HasPatient;
use App\Models\Payment;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MyPayments extends Page implements HasTable
{
    use HasPatient, InteractsWithTable;

    protected string $view = 'filament.patient.pages.my-payments';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-m-banknotes';

    protected static ?string $navigationLabel = 'Mes paiements';

    protected static string|\UnitEnum|null $navigationGroup = 'Mes documents';

    protected static ?int $navigationSort = 2;

    public function getHeading(): string
    {
        return 'Mes paiements';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                fn (): Builder => Payment::query()
                    ->where('patient_id', $this->getPatient()?->id)
                    ->with('invoice', 'paymentMethod')
                    ->latest('payment_date')
            )
            ->columns([
                TextColumn::make('payment_number')
                    ->label('N° Paiement')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('payment_date')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('invoice.invoice_number')
                    ->label('Facture')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('amount')
                    ->label('Montant')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 3, ',', ' ').' DT')
                    ->sortable(),

                TextColumn::make('paymentMethod.name')
                    ->label('Méthode')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'pending' => 'En attente',
                        'completed' => 'Encaissé',
                        'cancelled' => 'Annulé',
                        'refunded' => 'Remboursé',
                    ])
                    ->multiple(),
            ])
            ->defaultSort('payment_date', 'desc')
            ->paginated([10, 25, 50])
            ->emptyStateHeading('Aucun paiement')
            ->emptyStateDescription("Vous n'avez aucun paiement enregistré.");
    }
}
