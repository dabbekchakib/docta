<?php

namespace App\Filament\Resources\Refunds;

use App\Filament\Resources\Refunds\Pages\ListRefunds;
use App\Filament\Resources\Refunds\Pages\ViewRefund;
use App\Filament\Resources\Refunds\Schemas\RefundView;
use App\Filament\Resources\Refunds\Tables\RefundsTable;
use App\Models\Refund;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RefundResource extends Resource
{
    protected static ?string $model = Refund::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static string|\UnitEnum|null $navigationGroup = 'Facturation';

    protected static ?string $navigationLabel = 'Remboursements';

    protected static ?string $modelLabel = 'remboursement';

    protected static ?string $pluralModelLabel = 'remboursements';

    protected static ?int $navigationSort = 6;

    protected static ?string $recordTitleAttribute = 'refund_number';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyPermission([
            'refunds.view',
            'refunds.create',
            'refunds.approve',
            'refunds.reject',
        ]) ?? false;
    }

    /**
     * Les médecins ne voient que les remboursements liés à leurs patients.
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = auth()->user();

        if ($user?->hasRole('doctor')) {
            $doctorId = \App\Models\Doctor::query()->where('user_id', $user->id)->value('id');

            if ($doctorId) {
                $query->whereHas('payment.invoice', fn (Builder $q) => $q->where('doctor_id', $doctorId));
            } else {
                $query->whereKey(-1);
            }
        }

        return $query;
    }

    public static function infolist(Schema $schema): Schema
    {
        return RefundView::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RefundsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRefunds::route('/'),
            'view' => ViewRefund::route('/{record}'),
        ];
    }
}
