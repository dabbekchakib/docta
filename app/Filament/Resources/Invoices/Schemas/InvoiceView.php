<?php

namespace App\Filament\Resources\Invoices\Schemas;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;
use Spatie\Activitylog\Models\Activity;

class InvoiceView
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informations générales')
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('invoice_number')->label('N° facture'),
                        TextEntry::make('invoice_date')->label('Date')->date('d/m/Y'),
                        TextEntry::make('due_date')->label('Échéance')->date('d/m/Y')->placeholder('À réception'),
                        TextEntry::make('status')->label('Statut')
                            ->badge()
                            ->color(fn (InvoiceStatus $state): string => $state->getColor()),
                        TextEntry::make('patient.patient_number')->label('N° dossier patient'),
                        TextEntry::make('patient.full_name')->label('Patient'),
                        TextEntry::make('doctor.full_name')->label('Médecin')->placeholder('—'),
                        TextEntry::make('consultation.consultation_number')->label('Consultation liée')->placeholder('—'),
                        TextEntry::make('appointment.appointment_number')->label('Rendez-vous lié')->placeholder('—'),
                        TextEntry::make('laboratoryRequest.request_number')->label('Demande d\'examens liée')->placeholder('—'),
                        TextEntry::make('createdBy.name')->label('Créé par')->placeholder('—'),
                        TextEntry::make('created_at')->label('Créé le')->dateTime('d/m/Y H:i'),
                    ])
                    ->columns(3),
                Section::make('Montants')
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('subtotal')->label('Sous-total (TTC)')
                            ->formatStateUsing(fn ($state): string => number_format((float) $state, 3, ',', ' ').' DT')
                            ->alignEnd(),
                        TextEntry::make('discount_amount')->label('Remise')
                            ->formatStateUsing(fn ($state): string => number_format((float) $state, 3, ',', ' ').' DT')
                            ->color(fn (mixed $state): string => (float) $state > 0 ? 'success' : 'gray')
                            ->alignEnd(),
                        TextEntry::make('tax_amount')->label('TVA')
                            ->formatStateUsing(fn ($state): string => number_format((float) $state, 3, ',', ' ').' DT')
                            ->alignEnd(),
                        TextEntry::make('stamp_fee')->label('Timbre fiscal')
                            ->formatStateUsing(fn ($state): string => number_format((float) $state, 3, ',', ' ').' DT')
                            ->alignEnd(),
                        TextEntry::make('total')->label('Total à payer')
                            ->formatStateUsing(fn ($state): string => number_format((float) $state, 3, ',', ' ').' DT')
                            ->weight('bold')
                            ->size(TextSize::Large)
                            ->color('primary')
                            ->alignEnd(),
                        TextEntry::make('amount_paid')->label('Encaissé')
                            ->formatStateUsing(fn ($state): string => number_format((float) $state, 3, ',', ' ').' DT')
                            ->color('success')
                            ->alignEnd(),
                        TextEntry::make('amount_remaining')->label('Restant dû')
                            ->formatStateUsing(fn ($state): string => number_format((float) $state, 3, ',', ' ').' DT')
                            ->color(fn (mixed $state): string => (float) $state > 0 ? 'danger' : 'success')
                            ->alignEnd(),
                    ])
                    ->columns(3),
                Section::make('Prestations')
                    ->columnSpanFull()
                    ->schema([
                        RepeatableEntry::make('items')
                            ->label('Prestations facturées')
                            ->schema([
                                TextEntry::make('description')->label('Désignation')->weight('semibold'),
                                TextEntry::make('quantity')->label('Qté')
                                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 3, ',', ' ')),
                                TextEntry::make('unit_price')->label('Prix unitaire')
                                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 3, ',', ' ').' DT'),
                                TextEntry::make('discount_percent')->label('Remise')
                                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 2, ',', ' ').' %'),
                                TextEntry::make('line_base')->label('Base HT')
                                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 3, ',', ' ').' DT')
                                    ->alignEnd(),
                                TextEntry::make('tax_rate')->label('TVA')
                                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 2, ',', ' ').' %'),
                                TextEntry::make('tax_amount')->label('Montant TVA')
                                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 3, ',', ' ').' DT')
                                    ->alignEnd(),
                                TextEntry::make('line_total')->label('Total TTC')
                                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 3, ',', ' ').' DT')
                                    ->alignEnd()
                                    ->weight('semibold'),
                            ])
                            ->columns(3),
                    ]),
                Section::make('Paiements')
                    ->columnSpanFull()
                    ->schema([
                        RepeatableEntry::make('payments')
                            ->label('Paiements reçus')
                            ->state(fn (RepeatableEntry $component): array => self::resolvePayments($component))
                            ->schema([
                                TextEntry::make('payment_number')->label('N° paiement'),
                                TextEntry::make('payment_date')->label('Date')
                                    ->formatStateUsing(fn ($state): string => $state ? $state->format('d/m/Y') : '—'),
                                TextEntry::make('amount')->label('Montant')
                                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 3, ',', ' ').' DT')
                                    ->alignEnd()
                                    ->weight('semibold'),
                                TextEntry::make('payment_method')->label('Mode'),
                                TextEntry::make('status')->label('Statut')
                                    ->badge()
                                    ->color(fn (mixed $state): string => self::paymentStatusColor($state)),
                                TextEntry::make('reference')->label('Référence')->placeholder('—'),
                                TextEntry::make('received_by')->label('Utilisateur'),
                            ])
                            ->columns(4),
                    ]),
                Section::make('Notes')
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('notes')->label('Notes')->placeholder('—')->columnSpanFull(),
                    ]),
                Section::make('Historique')
                    ->columnSpanFull()
                    ->schema([
                        RepeatableEntry::make('activities')
                            ->label('Journal d\'activité')
                            ->state(fn (RepeatableEntry $component): array => self::resolveActivities($component))
                            ->schema([
                                TextEntry::make('created_at')->label('Date'),
                                TextEntry::make('description')->label('Action'),
                                TextEntry::make('causer.name')->label('Utilisateur'),
                            ])
                            ->columns(3),
                    ]),
            ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function resolvePayments(RepeatableEntry $component): array
    {
        $record = $component->getRecord();

        if (! $record instanceof Invoice) {
            return [];
        }

        return $record->payments()
            ->with('paymentMethod', 'receivedBy')
            ->latest('payment_date')
            ->get()
            ->map(fn ($payment): array => [
                'payment_number' => $payment->payment_number,
                'payment_date' => $payment->payment_date,
                'amount' => $payment->amount,
                'payment_method' => $payment->paymentMethod?->name,
                'status' => $payment->status,
                'reference' => $payment->reference,
                'received_by' => $payment->receivedBy?->name,
            ])
            ->all();
    }

    private static function paymentStatusColor(mixed $state): string
    {
        if ($state instanceof \App\Enums\PaymentStatus) {
            return $state->getColor();
        }

        $status = is_string($state) ? \App\Enums\PaymentStatus::tryFrom($state) : null;

        return $status?->getColor() ?? 'gray';
    }

    /**
     * @return array<int, mixed>
     */
    private static function resolveActivities(RepeatableEntry $component): array
    {
        $record = $component->getRecord();

        if (! $record instanceof Invoice) {
            return [];
        }

        return Activity::query()
            ->where('subject_type', $record->getMorphClass())
            ->where('subject_id', $record->getKey())
            ->with('causer')
            ->latest()
            ->limit(15)
            ->get()
            ->all();
    }
}
