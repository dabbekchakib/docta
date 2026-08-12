<?php

namespace App\Filament\Resources\CreditNotes;

use App\Filament\Resources\CreditNotes\Pages\ListCreditNotes;
use App\Filament\Resources\CreditNotes\Pages\ViewCreditNote;
use App\Filament\Resources\CreditNotes\Schemas\CreditNoteView;
use App\Filament\Resources\CreditNotes\Tables\CreditNotesTable;
use App\Models\CreditNote;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CreditNoteResource extends Resource
{
    protected static ?string $model = CreditNote::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentMinus;

    protected static string|\UnitEnum|null $navigationGroup = 'Facturation';

    protected static ?string $navigationLabel = 'Avoirs';

    protected static ?string $modelLabel = 'avoir';

    protected static ?string $pluralModelLabel = 'avoirs';

    protected static ?int $navigationSort = 5;

    protected static ?string $recordTitleAttribute = 'credit_note_number';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyPermission([
            'credit_notes.view',
            'credit_notes.create',
            'credit_notes.cancel',
        ]) ?? false;
    }

    /**
     * Les médecins ne voient que les avoirs liés à leurs factures.
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = auth()->user();

        if ($user?->hasRole('doctor')) {
            $doctorId = \App\Models\Doctor::query()->where('user_id', $user->id)->value('id');

            if ($doctorId) {
                $query->whereHas('invoice', fn (Builder $q) => $q->where('doctor_id', $doctorId));
            } else {
                $query->whereKey(-1);
            }
        }

        return $query;
    }

    public static function infolist(Schema $schema): Schema
    {
        return CreditNoteView::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CreditNotesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCreditNotes::route('/'),
            'view' => ViewCreditNote::route('/{record}'),
        ];
    }
}
