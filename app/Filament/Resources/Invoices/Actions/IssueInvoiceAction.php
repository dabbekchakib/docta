<?php

namespace App\Filament\Resources\Invoices\Actions;

use App\Services\InvoiceService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class IssueInvoiceAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'issueInvoice')
            ->label('Émettre')
            ->icon(Heroicon::OutlinedCheckCircle)
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Émettre la facture')
            ->modalDescription('La facture deviendra officielle et exigible. Les montants seront figés.')
            ->authorize('issue')
            ->visible(fn (Action $action): bool => auth()->user()?->can('issue', $action->getRecord()) ?? false)
            ->action(function (Action $action): void {
                app(InvoiceService::class)->issue($action->getRecord());

                Notification::make()
                    ->title('Facture émise')
                    ->success()
                    ->send();
            });
    }
}
