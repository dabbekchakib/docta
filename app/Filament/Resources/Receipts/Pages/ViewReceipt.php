<?php

namespace App\Filament\Resources\Receipts\Pages;

use App\Filament\Resources\Invoices\InvoiceResource;
use App\Filament\Resources\Payments\PaymentResource;
use App\Filament\Resources\Receipts\Actions\DownloadReceiptAction;
use App\Filament\Resources\Receipts\ReceiptResource;
use App\Models\Receipt;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class ViewReceipt extends ViewRecord
{
    protected static string $resource = ReceiptResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        activity('receipts')
            ->performedOn($this->record)
            ->causedBy(Auth::user())
            ->log('Fiche reçu consultée');
    }

    public function getHeader(): ?View
    {
        return view('filament.pages.stacked-header', [
            'breadcrumbs' => filament()->hasBreadcrumbs() ? $this->getBreadcrumbs() : [],
            'heading' => $this->getHeading(),
            'subheading' => $this->getSubheading(),
            'actions' => $this->getCachedHeaderActions(),
            'actionsAlignment' => $this->getHeaderActionsAlignment(),
            'scopes' => $this->getRenderHookScopes(),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('openInvoice')
                ->label('Voir la facture')
                ->icon(Heroicon::OutlinedReceiptPercent)
                ->color('gray')
                ->visible(fn (): bool => $this->record instanceof Receipt && $this->record->invoice)
                ->url(fn (): string => InvoiceResource::getUrl('view', ['record' => $this->record->invoice])),
            Action::make('openPayment')
                ->label('Voir le paiement')
                ->icon(Heroicon::OutlinedBanknotes)
                ->color('gray')
                ->visible(fn (): bool => $this->record instanceof Receipt && $this->record->payment)
                ->url(fn (): string => PaymentResource::getUrl('view', ['record' => $this->record->payment])),
            DownloadReceiptAction::make(),
        ];
    }
}
