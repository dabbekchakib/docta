<?php

namespace App\Filament\Resources\Refunds\Schemas;

use App\Enums\RefundStatus;
use App\Models\Refund;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;
use Spatie\Activitylog\Models\Activity;

class RefundView
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informations générales')
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('refund_number')->label('N° remboursement'),
                        TextEntry::make('refund_date')->label('Date')->date('d/m/Y'),
                        TextEntry::make('status')->label('Statut')
                            ->badge()
                            ->color(fn (RefundStatus $state): string => $state->getColor()),
                        TextEntry::make('payment.payment_number')->label('Paiement lié')->placeholder('—'),
                        TextEntry::make('creditNote.credit_note_number')->label('Avoir lié')->placeholder('—'),
                        TextEntry::make('patient.patient_number')->label('N° dossier patient'),
                        TextEntry::make('patient.full_name')->label('Patient'),
                        TextEntry::make('refund_method')->label('Méthode')->placeholder('—'),
                        TextEntry::make('reference')->label('Référence')->placeholder('—'),
                        TextEntry::make('createdBy.name')->label('Créé par')->placeholder('—'),
                        TextEntry::make('created_at')->label('Créé le')->dateTime('d/m/Y H:i'),
                    ])
                    ->columns(3),
                Section::make('Montant')
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('amount')->label('Montant remboursé')
                            ->formatStateUsing(fn ($state): string => number_format((float) $state, 3, ',', ' ').' DT')
                            ->weight('bold')
                            ->size(TextSize::Large)
                            ->color('warning'),
                    ]),
                Section::make('Motif et historique')
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('reason')->label('Motif')->placeholder('—')->columnSpanFull(),
                        TextEntry::make('rejected_reason')->label('Motif de refus')->placeholder('—')->columnSpanFull(),
                        TextEntry::make('requested_at')->label('Demandé le')->dateTime('d/m/Y H:i')->placeholder('—'),
                        TextEntry::make('approved_at')->label('Approuvé le')->dateTime('d/m/Y H:i')->placeholder('—'),
                        TextEntry::make('completed_at')->label('Exécuté le')->dateTime('d/m/Y H:i')->placeholder('—'),
                        TextEntry::make('approvedBy.name')->label('Approuvé par')->placeholder('—'),
                    ])
                    ->columns(3),
                Section::make('Journal d\'activité')
                    ->columnSpanFull()
                    ->schema([
                        RepeatableEntry::make('activities')
                            ->label('Activités récentes')
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
     * @return array<int, mixed>
     */
    private static function resolveActivities(RepeatableEntry $component): array
    {
        $record = $component->getRecord();

        if (! $record instanceof Refund) {
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
