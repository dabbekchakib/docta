<?php

namespace App\Filament\Resources\Secretaries\Tables;

use App\Enums\Governorate;
use App\Enums\SecretaryGender;
use App\Enums\SecretaryStatus;
use App\Filament\Resources\Secretaries\Tables\Actions\ToggleSecretaryStatusAction;
use App\Models\Secretary;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class SecretariesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('photo')
                    ->label('Photo')
                    ->collection('photo')
                    ->circular()
                    ->width(40)
                    ->height(40)
                    ->defaultImageUrl(fn (Model $record): string => 'https://ui-avatars.com/api/?name=' . urlencode($record->first_name . ' ' . $record->last_name) . '&size=64&background=6366f1&color=fff&bold=true'),
                TextColumn::make('secretary_code')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                TextColumn::make('full_name')
                    ->label('Nom complet')
                    ->state(fn (Model $record): string => trim($record->first_name . ' ' . $record->last_name))
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function (Builder $sub) use ($search): void {
                            $sub->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhereRaw('CONCAT(first_name, " ", last_name) LIKE ?', ["%{$search}%"]);
                        });
                    })
                    ->sortable(['last_name', 'first_name']),
                TextColumn::make('gender')
                    ->label('Sexe')
                    ->formatStateUsing(fn (SecretaryGender $state): string => $state->getLabel())
                    ->badge()
                    ->color('gray')
                    ->toggleable(),
                TextColumn::make('phone')
                    ->label('Téléphone')
                    ->icon('heroicon-m-phone')
                    ->toggleable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->icon('heroicon-m-envelope')
                    ->toggleable(),
                TextColumn::make('city')
                    ->label('Ville')
                    ->toggleable(),
                TextColumn::make('user.name')
                    ->label('Compte')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Créée le')
                    ->sortable()
                    ->date('d/m/Y')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('gender')
                    ->label('Sexe')
                    ->options(SecretaryGender::options()),
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(SecretaryStatus::options()),
                SelectFilter::make('governorate')
                    ->label('Gouvernorat')
                    ->options(Governorate::options())
                    ->searchable(),
                Filter::make('created_at')
                    ->label('Date de création')
                    ->form([
                        DatePicker::make('created_from')->label('Du'),
                        DatePicker::make('created_until')->label('Au'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['created_from'], fn (Builder $sub, $date): Builder => $sub->whereDate('created_at', '>=', $date))
                            ->when($data['created_until'], fn (Builder $sub, $date): Builder => $sub->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                ToggleSecretaryStatusAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('bulkToggleStatus')
                        ->label('Activer / Désactiver')
                        ->icon('heroicon-m-power')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records): void {
                            $records->each(function (Secretary $secretary): void {
                                $newStatus = $secretary->status === SecretaryStatus::Active
                                    ? SecretaryStatus::Inactive
                                    : SecretaryStatus::Active;

                                $secretary->status = $newStatus;
                                $secretary->save();

                                if ($secretary->user) {
                                    $secretary->user->is_active = $newStatus === SecretaryStatus::Active;
                                    $secretary->user->save();
                                }
                            });
                        }),
                    DeleteBulkAction::make()
                        ->before(function (Collection $records): void {
                            $records->each(function (Secretary $secretary): void {
                                if ($secretary->user) {
                                    $secretary->user->is_active = false;
                                    $secretary->user->save();
                                }
                            });
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
