<?php

namespace App\Filament\Resources\LaboratoryRequests\Actions;

use App\Filament\Resources\LaboratoryRequests\Pages\EnterResults;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;

class EnterResultsAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'enterResults')
            ->label('Saisir les résultats')
            ->icon(Heroicon::OutlinedPencilSquare)
            ->color('warning')
            ->authorize('enterResults')
            ->visible(fn (Action $action): bool => auth()->user()?->can('enterResults', $action->getRecord()) ?? false)
            ->url(fn (Action $action): string => EnterResults::getUrl(['record' => $action->getRecord()]));
    }
}
