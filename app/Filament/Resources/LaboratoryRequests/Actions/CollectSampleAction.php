<?php

namespace App\Filament\Resources\LaboratoryRequests\Actions;

use App\Models\LaboratoryRequestItem;
use App\Services\LaboratoryRequestService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class CollectSampleAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'collectSample')
            ->label('Collecter un prélèvement')
            ->icon(Heroicon::OutlinedBeaker)
            ->color('primary')
            ->authorize('manageSamples')
            ->visible(fn (Action $action): bool => self::canCollect($action))
            ->modalHeading('Collecter un prélèvement')
            ->form([
                Select::make('item_id')
                    ->label('Examen')
                    ->options(fn (Action $action): array => self::samplableItems($action))
                    ->required()
                    ->native(false),
                Textarea::make('notes')
                    ->label('Notes')
                    ->rows(2),
            ])
            ->action(function (Action $action, array $data): void {
                app(LaboratoryRequestService::class)->collectSample(
                    $action->getRecord(),
                    (int) $data['item_id'],
                    $data['notes'] ?? null,
                );

                Notification::make()
                    ->title('Prélèvement enregistré')
                    ->success()
                    ->send();
            });
    }

    private static function canCollect(Action $action): bool
    {
        $request = $action->getRecord();

        if (! $request || ! auth()->user()?->can('manageSamples', $request)) {
            return false;
        }

        return self::samplableItems($action) !== [];
    }

    /**
     * Examens de la demande n'ayant pas encore de prélèvement non rejeté.
     *
     * @return array<int, string>
     */
    private static function samplableItems(Action $action): array
    {
        $request = $action->getRecord();

        if (! $request) {
            return [];
        }

        $sampledItemIds = $request->samples()
            ->where('status', '!=', 'rejected')
            ->pluck('laboratory_request_item_id')
            ->all();

        return $request->items()
            ->with('test')
            ->whereNotIn('id', $sampledItemIds)
            ->get()
            ->mapWithKeys(fn (LaboratoryRequestItem $item): array => [
                $item->id => $item->test?->name ?? "Examen #{$item->id}",
            ])
            ->all();
    }
}
