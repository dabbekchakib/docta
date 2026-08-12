<?php

namespace App\Filament\Resources\Invoices\Actions;

use App\Filament\Resources\Payments\PaymentResource;
use App\Models\Invoice;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;

class ViewPaymentsAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'viewPayments')
            ->label('Voir les paiements')
            ->icon(Heroicon::OutlinedBanknotes)
            ->color('gray')
            ->visible(fn (Action $action): bool => $action->getRecord() instanceof Invoice
                && (auth()->user()?->can('viewAny', \App\Models\Payment::class) ?? false))
            ->url(fn (Action $action): string => PaymentResource::getUrl('index', [
                'tableFilters' => ['invoice_id' => ['value' => $action->getRecord()->id]],
            ]));
    }
}
