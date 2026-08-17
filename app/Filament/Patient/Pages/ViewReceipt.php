<?php

namespace App\Filament\Patient\Pages;

use App\Filament\Patient\Pages\Concerns\HasPatient;
use App\Models\Receipt;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Infolist;
use Filament\Pages\Page;
use Filament\Panel;

use BackedEnum;

class ViewReceipt extends Page implements HasInfolists
{
    use HasPatient, InteractsWithInfolists;

    protected string $view = 'filament.patient.pages.view-receipt';

    protected static BackedEnum|string|null $navigationIcon = null;

    protected ?Receipt $receipt = null;

    public function getHeading(): string
    {
        return 'Reçu de paiement';
    }

    public function mount(int $receiptId): void
    {
        $patient = $this->getPatient();

        if (! $patient) {
            abort(403);
        }

        $this->receipt = Receipt::with(['payment.paymentMethod', 'invoice', 'patient'])
            ->where('id', $receiptId)
            ->where('patient_id', $patient->id)
            ->firstOrFail();
    }

    public static function getRoutePath(Panel $panel): string
    {
        return '/receipt/{receiptId}';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public function infolist(Infolist $infolist): Infolist
    {
        $receipt = $this->receipt;

        return $infolist
            ->schema([
                Section::make('Informations du reçu')
                    ->icon('heroicon-m-document-text')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('receipt_number')
                            ->label('N° Reçu'),
                        TextEntry::make('receipt_date')
                            ->label('Date')
                            ->date('d/m/Y'),
                        TextEntry::make('amount')
                            ->label('Montant')
                            ->formatStateUsing(fn ($state): string => number_format((float) $state, 3, ',', ' ').' DT')
                            ->weight('bold'),
                    ]),

                Section::make('Paiement')
                    ->icon('heroicon-m-banknotes')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('payment.paymentMethod.name')
                            ->label('Méthode de paiement')
                            ->placeholder('—'),
                        TextEntry::make('payment.reference')
                            ->label('Référence')
                            ->placeholder('—'),
                        TextEntry::make('payment.payment_date')
                            ->label('Date du paiement')
                            ->date('d/m/Y'),
                        TextEntry::make('payment.amount')
                            ->label('Montant du paiement')
                            ->formatStateUsing(fn ($state): string => number_format((float) $state, 3, ',', ' ').' DT'),
                    ]),

                Section::make('Facture associée')
                    ->icon('heroicon-m-receipt-percent')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('invoice.invoice_number')
                            ->label('N° Facture')
                            ->placeholder('—'),
                        TextEntry::make('invoice.invoice_date')
                            ->label('Date de facture')
                            ->date('d/m/Y'),
                        TextEntry::make('invoice.total')
                            ->label('Total facture')
                            ->formatStateUsing(fn ($state): string => number_format((float) $state, 3, ',', ' ').' DT'),
                        TextEntry::make('invoice.status')
                            ->label('Statut facture')
                            ->badge(),
                    ]),

                Section::make('Patient')
                    ->icon('heroicon-m-user')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('patient.full_name')
                            ->label('Nom complet'),
                        TextEntry::make('patient.patient_number')
                            ->label('N° Patient'),
                        TextEntry::make('patient.phone')
                            ->label('Téléphone'),
                        TextEntry::make('patient.email')
                            ->label('Email'),
                    ]),
            ]);
    }
}
