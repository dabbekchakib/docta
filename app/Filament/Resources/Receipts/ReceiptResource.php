<?php

namespace App\Filament\Resources\Receipts;

use App\Filament\Resources\Receipts\Pages\ListReceipts;
use App\Filament\Resources\Receipts\Pages\ViewReceipt;
use App\Filament\Resources\Receipts\Schemas\ReceiptView;
use App\Filament\Resources\Receipts\Tables\ReceiptsTable;
use App\Models\Receipt;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReceiptResource extends Resource
{
    protected static ?string $model = Receipt::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptRefund;

    protected static string|\UnitEnum|null $navigationGroup = 'Facturation';

    protected static ?string $navigationLabel = 'Reçus';

    protected static ?string $modelLabel = 'reçu';

    protected static ?string $pluralModelLabel = 'reçus';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'receipt_number';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyPermission([
            'receipts.view',
            'receipts.create',
            'receipts.download',
        ]) ?? false;
    }

    /**
     * Les médecins ne voient que les reçus liés à leurs patients.
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
        return ReceiptView::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReceiptsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReceipts::route('/'),
            'view' => ViewReceipt::route('/{record}'),
        ];
    }
}
