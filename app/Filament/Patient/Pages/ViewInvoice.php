<?php

namespace App\Filament\Patient\Pages;

use App\Filament\Patient\Pages\Concerns\HasPatient;
use App\Models\Invoice;
use App\Services\InvoicePdfService;
use App\Services\SettingsService;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

use BackedEnum;

class ViewInvoice extends Page
{
    use HasPatient, InteractsWithInfolists;

    protected string $view = 'filament.patient.pages.view-invoice';

    protected static BackedEnum|string|null $navigationIcon = null;

    public ?int $invoiceId = null;

    protected ?Invoice $invoice = null;

    public function getHeading(): string
    {
        return 'Facture';
    }

    public function mount(int $invoiceId): void
    {
        $this->invoiceId = $invoiceId;

        $patient = $this->getPatient();

        if (! $patient) {
            abort(403);
        }

        $this->invoice = Invoice::with(['items', 'payments', 'patient'])
            ->where('id', $invoiceId)
            ->where('patient_id', $patient->id)
            ->firstOrFail();
    }

    public static function getRoutePath(Panel $panel): string
    {
        return '/invoice/{invoiceId}';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadPdf')
                ->label('Télécharger PDF')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->color('primary')
                ->action(function (): void {
                    $invoice = Invoice::with(['items', 'payments', 'patient'])
                        ->where('id', $this->invoiceId)
                        ->where('patient_id', $this->getPatient()?->id)
                        ->firstOrFail();

                    $pdf = Pdf::loadView('pdf.invoice', [
                        'invoice' => $invoice,
                        'cabinet' => app(\App\Services\SettingsService::class)->cabinet(),
                    ])->setPaper('a4', 'portrait');

                    $filename = 'facture-'.$invoice->invoice_number.'.pdf';
                    $base64 = base64_encode($pdf->output());

                    $this->dispatch('downloadPdf', filename: $filename, content: $base64);
                }),
        ];
    }

    public function content(Schema $schema): Schema
    {
        $invoice = $this->invoice ??= $this->loadInvoice();

        return $schema
            ->record($invoice)
            ->schema([
                Section::make('Informations de la facture')
                    ->icon('heroicon-m-document-text')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('invoice_number')
                            ->label('N° Facture'),
                        TextEntry::make('invoice_date')
                            ->label('Date')
                            ->date('d/m/Y'),
                        TextEntry::make('due_date')
                            ->label('Échéance')
                            ->date('d/m/Y'),
                        TextEntry::make('status')
                            ->label('Statut')
                            ->badge(),
                    ]),

                Section::make('Détails')
                    ->icon('heroicon-m-list-bullet')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->label('Lignes de facture')
                            ->schema([
                                TextEntry::make('description')
                                    ->label('Description'),
                                TextEntry::make('quantity')
                                    ->label('Qté')
                                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 3, ',', ' ')),
                                TextEntry::make('unit_price')
                                    ->label('Prix unitaire')
                                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 3, ',', ' ').' DT'),
                                TextEntry::make('line_total')
                                    ->label('Total')
                                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 3, ',', ' ').' DT'),
                            ])
                            ->columns(4),
                    ]),

                Section::make('Totaux')
                    ->icon('heroicon-m-calculator')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('subtotal')
                            ->label('Sous-total')
                            ->formatStateUsing(fn ($state): string => number_format((float) $state, 3, ',', ' ').' DT'),
                        TextEntry::make('discount_amount')
                            ->label('Remise')
                            ->formatStateUsing(fn ($state): string => number_format((float) $state, 3, ',', ' ').' DT')
                            ->default(0),
                        TextEntry::make('tax_amount')
                            ->label('TVA')
                            ->formatStateUsing(fn ($state): string => number_format((float) $state, 3, ',', ' ').' DT')
                            ->default(0),
                        TextEntry::make('stamp_fee')
                            ->label('Timbre')
                            ->formatStateUsing(fn ($state): string => number_format((float) $state, 3, ',', ' ').' DT')
                            ->default(0),
                        TextEntry::make('total')
                            ->label('Total TTC')
                            ->formatStateUsing(fn ($state): string => number_format((float) $state, 3, ',', ' ').' DT')
                            ->weight('bold'),
                    ]),

                Section::make('Paiements')
                    ->icon('heroicon-m-banknotes')
                    ->schema([
                        RepeatableEntry::make('payments')
                            ->label('Paiements effectués')
                            ->schema([
                                TextEntry::make('payment_date')
                                    ->label('Date')
                                    ->date('d/m/Y'),
                                TextEntry::make('amount')
                                    ->label('Montant')
                                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 3, ',', ' ').' DT'),
                            ])
                            ->columns(2)
                            ->placeholder('Aucun paiement enregistré'),
                    ]),

                Section::make('Reste à payer')
                    ->icon('heroicon-m-exclamation-circle')
                    ->schema([
                        TextEntry::make('amount_remaining')
                            ->label('Montant restant')
                            ->formatStateUsing(fn ($state): string => number_format((float) $state, 3, ',', ' ').' DT')
                            ->color(fn ($state): ?string => (float) $state > 0 ? 'danger' : 'success')
                            ->weight('bold'),
                    ]),
            ]);
    }

    private function loadInvoice(): ?Invoice
    {
        $patient = $this->getPatient();

        if (! $patient || ! $this->invoiceId) {
            return null;
        }

        return Invoice::with(['items', 'payments', 'patient'])
            ->where('id', $this->invoiceId)
            ->where('patient_id', $patient->id)
            ->firstOrFail();
    }
}
