<?php

namespace App\Filament\Patient\Pages;

use App\Enums\InvoiceStatus;
use App\Filament\Patient\Pages\Concerns\HasPatient;
use App\Models\Invoice;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MyInvoices extends Page implements HasTable
{
    use HasPatient, InteractsWithTable;

    protected string $view = 'filament.patient.pages.my-invoices';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-m-receipt-percent';

    protected static string|\UnitEnum|null $navigationGroup = 'Mes documents';

    protected static ?int $navigationSort = 1;

    public function getHeading(): string
    {
        return 'Mes factures';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                fn (): Builder => Invoice::query()
                    ->where('patient_id', $this->getPatient()?->id)
                    ->where('status', '!=', InvoiceStatus::Draft)
                    ->latest('invoice_date')
            )
            ->columns([
                TextColumn::make('invoice_number')
                    ->label('N° Facture')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('invoice_date')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('total')
                    ->label('Total')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 3, ',', ' ').' DT')
                    ->sortable(),

                TextColumn::make('amount_paid')
                    ->label('Payé')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 3, ',', ' ').' DT')
                    ->sortable(),

                TextColumn::make('amount_remaining')
                    ->label('Reste à payer')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 3, ',', ' ').' DT')
                    ->sortable()
                    ->color(fn ($state): ?string => (float) $state > 0 ? 'danger' : null),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(InvoiceStatus::options())
                    ->multiple(),
            ])
            ->actions([
                Action::make('view')
                    ->label('Voir')
                    ->icon('heroicon-m-eye')
                    ->url(fn (Invoice $record): string => ViewInvoice::getUrl(['invoiceId' => $record->id])),
            ])
            ->defaultSort('invoice_date', 'desc')
            ->paginated([10, 25, 50])
            ->emptyStateHeading('Aucune facture')
            ->emptyStateDescription("Vous n'avez aucune facture.");
    }
}
