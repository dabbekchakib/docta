<?php

namespace App\Filament\Resources\Payments\Pages;

use App\Enums\PaymentStatus;
use App\Filament\Resources\Invoices\InvoiceResource;
use App\Filament\Resources\Payments\Actions\CancelPaymentAction;
use App\Filament\Resources\Payments\Actions\DownloadPaymentReceiptAction;
use App\Filament\Resources\Payments\Actions\RequestRefundAction;
use App\Filament\Resources\Payments\Actions\ValidatePaymentAction;
use App\Filament\Resources\Payments\PaymentResource;
use App\Filament\Resources\Receipts\ReceiptResource;
use App\Models\Payment;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
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
            Action::make('viewReceipt')
                ->label('Voir le reçu')
                ->icon(Heroicon::OutlinedReceiptRefund)
                ->color('gray')
                ->visible(fn (): bool => $this->record instanceof Payment && $this->record->receipt)
                ->url(fn (): string => ReceiptResource::getUrl('view', ['record' => $this->record->receipt])),
            EditAction::make()
                ->label('Modifier')
                ->visible(fn (): bool => $this->record instanceof Payment
                    && $this->record->status === PaymentStatus::Pending
                    && (auth()->user()?->can('update', $this->record) ?? false)),
            ValidatePaymentAction::make(),
            DownloadPaymentReceiptAction::make(),
            RequestRefundAction::make(),
            CancelPaymentAction::make(),
        ];
    }
}
