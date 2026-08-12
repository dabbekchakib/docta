<?php

namespace App\Filament\Resources\Payments\Schemas;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;
use Spatie\Activitylog\Models\Activity;

class PaymentView
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informations générales')
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('payment_number')->label('N° paiement'),
                        TextEntry::make('payment_date')->label('Date')->date('d/m/Y'),
                        TextEntry::make('status')->label('Statut')
                            ->badge()
                            ->color(fn (PaymentStatus $state): string => $state->getColor()),
                        TextEntry::make('payment_method.name')->label('Moyen de paiement')->placeholder('—'),
                        TextEntry::make('reference')->label('Référence')->placeholder('—'),
                        TextEntry::make('receivedBy.name')->label('Encaissé par')->placeholder('—'),
                        TextEntry::make('invoice.invoice_number')->label('Facture liée')->placeholder('—'),
                        TextEntry::make('patient.patient_number')->label('N° dossier patient'),
                        TextEntry::make('patient.full_name')->label('Patient'),
                        TextEntry::make('receipt.receipt_number')->label('Reçu émis')->placeholder('—'),
                        TextEntry::make('created_at')->label('Créé le')->dateTime('d/m/Y H:i'),
                    ])
                    ->columns(3),
                Section::make('Montant')
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('amount')->label('Montant encaissé')
                            ->formatStateUsing(fn ($state): string => number_format((float) $state, 3, ',', ' ').' DT')
                            ->weight('bold')
                            ->size(TextSize::Large)
                            ->color('success'),
                    ]),
                Section::make('Notes')
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('notes')->label('Notes')->placeholder('—')->columnSpanFull(),
                        TextEntry::make('cancelled_reason')->label('Motif d\'annulation')->placeholder('—')->columnSpanFull(),
                    ]),
                Section::make('Journal d\'activité')
                    ->columnSpanFull()
                    ->schema([
                        RepeatableEntry::make('activities')
                            ->label('Activités récentes')
                            ->state(fn (RepeatableEntry $component): array => self::resolveActivities($component))
                            ->schema([
                                TextEntry::make('created_at')->label('Date'),
                                TextEntry::make('description')->label('Action'),
                                TextEntry::make('causer')->label('Utilisateur'),
                            ])
                            ->columns(3),
                    ]),
            ]);
    }

    /**
     * @return array<int, mixed>
     */
    private static function resolveActivities(RepeatableEntry $component): array
    {
        $record = $component->getRecord();

        if (! $record instanceof Payment) {
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
