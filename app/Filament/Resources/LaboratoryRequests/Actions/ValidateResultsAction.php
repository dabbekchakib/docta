<?php

namespace App\Filament\Resources\LaboratoryRequests\Actions;

use App\Filament\Resources\LaboratoryRequests\Pages\ValidateResults;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;

class ValidateResultsAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'validateResults')
            ->label('Valider les résultats')
            ->icon(Heroicon::OutlinedCheckBadge)
            ->color('success')
            ->authorize('validateResults')
            ->visible(fn (Action $action): bool => auth()->user()?->can('validateResults', $action->getRecord()) ?? false)
            ->url(fn (Action $action): string => ValidateResults::getUrl(['record' => $action->getRecord()]));
    }
}
