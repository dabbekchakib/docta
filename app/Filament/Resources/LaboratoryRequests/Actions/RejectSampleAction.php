<?php

namespace App\Filament\Resources\LaboratoryRequests\Actions;

use App\Models\Sample;
use App\Services\LaboratoryRequestService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class RejectSampleAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'rejectSample')
            ->label('Rejeter un prélèvement')
            ->icon(Heroicon::OutlinedXCircle)
            ->color('danger')
            ->authorize('manageSamples')
            ->visible(fn (Action $action): bool => self::canReject($action))
            ->modalHeading('Rejeter un prélèvement')
            ->form([
                Select::make('sample_id')
                    ->label('Prélèvement')
                    ->options(fn (Action $action): array => self::rejectableSamples($action))
                    ->required()
                    ->native(false),
                Textarea::make('reason')
                    ->label('Motif du rejet')
                    ->rows(3)
                    ->required(),
            ])
            ->action(function (Action $action, array $data): void {
                /** @var Sample|null $sample */
                $sample = $action->getRecord()->samples()->find((int) $data['sample_id']);

                if (! $sample) {
                    abort(422, 'Prélèvement introuvable.');
                }

                app(LaboratoryRequestService::class)->rejectSample($sample, $data['reason']);

                Notification::make()
                    ->title('Prélèvement rejeté')
                    ->success()
                    ->send();
            });
    }

    private static function canReject(Action $action): bool
    {
        $request = $action->getRecord();

        if (! $request || ! auth()->user()?->can('manageSamples', $request)) {
            return false;
        }

        return self::rejectableSamples($action) !== [];
    }

    /**
     * @return array<int, string>
     */
    private static function rejectableSamples(Action $action): array
    {
        $request = $action->getRecord();

        if (! $request) {
            return [];
        }

        return $request->samples()
            ->where('status', '!=', 'rejected')
            ->get()
            ->mapWithKeys(fn (Sample $sample): array => [
                $sample->id => $sample->sample_number.' — '.($sample->item?->test?->name ?? 'Examen'),
            ])
            ->all();
    }
}
