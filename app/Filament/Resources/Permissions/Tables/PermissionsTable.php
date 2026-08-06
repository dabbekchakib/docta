<?php

namespace App\Filament\Resources\Permissions\Tables;

use App\Enums\Permission as PermissionEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PermissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->withCount('roles'))
            ->columns([
                TextColumn::make('name')
                    ->label('Nom technique')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                TextColumn::make('name')
                    ->label('Libellé')
                    ->formatStateUsing(fn ($state): string => PermissionEnum::tryFrom($state)?->label() ?? $state),
                TextColumn::make('guard_name')
                    ->label('Guard')
                    ->sortable(),
                TextColumn::make('roles_count')
                    ->label('Rôles')
                    ->counts('roles')
                    ->sortable()
                    ->badge()
                    ->color('primary'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
