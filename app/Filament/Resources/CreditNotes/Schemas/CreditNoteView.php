<?php

namespace App\Filament\Resources\CreditNotes\Schemas;

use App\Enums\CreditNoteStatus;
use App\Models\CreditNote;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;
use Spatie\Activitylog\Models\Activity;

class CreditNoteView
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informations générales')
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('credit_note_number')->label('N° avoir'),
                        TextEntry::make('credit_note_date')->label('Date')->date('d/m/Y'),
                        TextEntry::make('status')->label('Statut')
                            ->badge()
                            ->color(fn (CreditNoteStatus $state): string => $state->getColor()),
                        TextEntry::make('invoice.invoice_number')->label('Facture liée')->placeholder('—'),
                        TextEntry::make('patient.patient_number')->label('N° dossier patient'),
                        TextEntry::make('patient.full_name')->label('Patient'),
                        TextEntry::make('createdBy.name')->label('Créé par')->placeholder('—'),
                        TextEntry::make('created_at')->label('Créé le')->dateTime('d/m/Y H:i'),
                    ])
                    ->columns(3),
                Section::make('Montant')
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('amount')->label('Montant de l\'avoir')
                            ->formatStateUsing(fn ($state): string => number_format((float) $state, 3, ',', ' ').' DT')
                            ->weight('bold')
                            ->size(TextSize::Large)
                            ->color('warning'),
                    ]),
                Section::make('Motif et historique')
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('reason')->label('Motif')->placeholder('—')->columnSpanFull(),
                        TextEntry::make('cancelled_reason')->label('Motif d\'annulation')->placeholder('—')->columnSpanFull(),
                        TextEntry::make('refunds.refund_number')->label('Remboursements liés')->placeholder('—')->listWithLineBreaks(),
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

        if (! $record instanceof CreditNote) {
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
