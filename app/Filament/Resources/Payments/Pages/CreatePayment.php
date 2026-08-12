<?php

namespace App\Filament\Resources\Payments\Pages;

use App\Filament\Resources\Payments\PaymentResource;
use App\Models\Invoice;
use App\Services\PaymentService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreatePayment extends CreateRecord
{
    protected static string $resource = PaymentResource::class;

    public function mount(): void
    {
        parent::mount();

        $invoiceId = (int) request()->query('invoice');

        if ($invoiceId > 0) {
            $invoice = Invoice::query()->with('patient')->find($invoiceId);

            if ($invoice) {
                $this->form->fill([
                    'invoice_id' => $invoice->id,
                    'patient_id' => $invoice->patient_id,
                ]);
            }
        }
    }

    protected function handleRecordCreation(array $data): Model
    {
        return app(PaymentService::class)->create($data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
