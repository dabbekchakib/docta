<?php

namespace App\Filament\Resources\Payments\Pages;

use App\Filament\Resources\Invoices\InvoiceResource;
use App\Filament\Resources\Payments\Actions\CancelPaymentAction;
use App\Filament\Resources\Payments\Actions\DownloadPaymentReceiptAction;
use App\Filament\Resources\Payments\Actions\RequestRefundAction;
use App\Filament\Resources\Payments\PaymentResource;
use App\Filament\Resources\Payments\Schemas\PaymentView;
use App\Models\Payment;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class ViewPayment extends ViewRecord
{
    protected static string $resource = PaymentResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        activity('payments')
            ->performedOn($this->record)
            ->causedBy(Auth::user())
            ->log('Fiche encaissement consultée');
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
                ->visible(fn (): bool => $this->record instanceof Payment && $this->record->invoice)
                ->url(fn (): string => InvoiceResource::getUrl('view', ['record' => $this->record->invoice])),
            DownloadPaymentReceiptAction::make(),
            RequestRefundAction::make(),
            CancelPaymentAction::make(),
        ];
    }
}
