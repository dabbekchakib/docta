<?php

namespace App\Filament\Resources\Payments\Schemas;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Models\Invoice;
use App\Models\Patient;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informations du paiement')
                    ->description('Le patient est déduit automatiquement de la facture sélectionnée.')
                    ->columnSpanFull()
                    ->schema([
                        Select::make('invoice_id')
                            ->label('Facture')
                            ->relationship('invoice', 'invoice_number')
                            ->getOptionLabelFromRecordUsing(fn (Invoice $record): string => $record->invoice_number.' — '.$record->patient?->full_name)
                            ->getSearchResultsUsing(function (?string $search): array {
                                return Invoice::query()
                                    ->with('patient')
                                    ->where('status', '!=', InvoiceStatus::Credited->value)
                                    ->where('amount_remaining', '>', 0)
                                    ->when($search, fn ($query, string $search) => $query->where('invoice_number', 'like', "%{$search}%"))
                                    ->latest('invoice_date')
                                    ->limit(20)
                                    ->get()
                                    ->mapWithKeys(fn (Invoice $invoice): array => [
                                        $invoice->id => $invoice->invoice_number.' — '.($invoice->patient?->full_name ?? '—'),
                                    ])
                                    ->all();
                            })
                            ->searchable()
                            ->preload()
                            ->live()
                            ->disabled(fn (string $operation): bool => $operation === 'edit')
                            ->afterStateUpdated(function ($state, $set): void {
                                $invoice = $state ? Invoice::query()->with('patient')->find((int) $state) : null;

                                if ($invoice) {
                                    $set('patient_id', $invoice->patient_id);
                                }
                            })
                            ->required(),
                        Select::make('patient_id')
                            ->label('Patient')
                            ->relationship('patient', 'full_name')
                            ->getOptionLabelFromRecordUsing(fn (Patient $record): string => $record->patient_number.' — '.$record->full_name)
                            ->disabled()
                            ->dehydrated(false)
                            ->searchable()
                            ->preload()
                            ->helperText('Déduit de la facture sélectionnée.'),
                        Select::make('payment_method_id')
                            ->label('Mode de paiement')
                            ->relationship('paymentMethod', 'name')
                            ->options(fn (): array => \App\Models\PaymentMethod::query()
                                ->where('is_active', true)
                                ->orderBy('name')
                                ->get()
                                ->pluck('name', 'id')
                                ->mapWithKeys(fn (string $name, int $id): array => [$id => \Illuminate\Support\Str::upper($name)])
                                ->all())
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false),
                        DatePicker::make('payment_date')
                            ->label('Date du paiement')
                            ->displayFormat('d/m/Y')
                            ->native(false)
                            ->default(now()->toDateString())
                            ->required(),
                        TextInput::make('amount')
                            ->label('Montant (TND)')
                            ->numeric()
                            ->required()
                            ->minValue(0.001)
                            ->step('0.001')
                            ->prefix('DT')
                            ->maxValue(fn (Get $get): ?float => self::amountRemaining($get))
                            ->helperText(fn (Get $get): string => self::amountHelper($get))
                            ->validationMessages([
                                'max' => 'Le montant ne peut pas dépasser le restant dû de la facture.',
                            ]),
                        TextInput::make('reference')
                            ->label('Référence')
                            ->placeholder('N° de chèque, de carte, virement…')
                            ->nullable()
                            ->maxLength(255),
                        Select::make('status')
                            ->label('Statut')
                            ->options([
                                PaymentStatus::Pending->value => 'En attente',
                                PaymentStatus::Completed->value => 'Effectué',
                            ])
                            ->default(PaymentStatus::Pending->value)
                            ->live()
                            ->native(false)
                            ->helperText(fn ($state): string => $state === PaymentStatus::Completed->value
                                ? 'Le paiement sera encaissé immédiatement et un reçu sera émis.'
                                : 'Un paiement en attente peut être modifié avant validation.'),
                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(3),
            ]);
    }

    /**
     * Montant restant dû de la facture sélectionnée, ou null si aucune.
     */
    private static function amountRemaining(Get $get): ?float
    {
        $invoiceId = (int) ($get('invoice_id') ?? 0);

        if ($invoiceId <= 0) {
            return null;
        }

        $remaining = Invoice::query()->whereKey($invoiceId)->value('amount_remaining');

        return $remaining === null ? null : (float) $remaining;
    }

    private static function amountHelper(Get $get): string
    {
        $remaining = self::amountRemaining($get);

        if ($remaining === null) {
            return 'Sélectionnez une facture pour afficher le restant dû.';
        }

        return 'Restant dû : '.number_format($remaining, 3, ',', ' ').' DT';
    }
}
