<?php

namespace App\Services;

use App\Models\Setting;

class SettingsService
{
    /**
     * Paramètres du cabinet utilisés dans les documents (PDF) et la facturation.
     *
     * @param  array<string, string|null>  $data
     */
    public function update(array $data): void
    {
        foreach ($data as $key => $value) {
            Setting::set($key, $value);
        }
    }

    public function get(string $key, ?string $default = null): ?string
    {
        return Setting::value($key, $default);
    }

    public function name(): string
    {
        return $this->get('cabinet_name', 'Cabinet Médical');
    }

    public function address(): ?string
    {
        return $this->get('cabinet_address');
    }

    public function phone(): ?string
    {
        return $this->get('cabinet_phone');
    }

    public function email(): ?string
    {
        return $this->get('cabinet_email');
    }

    public function fiscalNumber(): ?string
    {
        return $this->get('cabinet_fiscal_number');
    }

    public function rib(): ?string
    {
        return $this->get('cabinet_rib');
    }

    public function logoPath(): ?string
    {
        return $this->get('cabinet_logo');
    }

    public function currency(): string
    {
        return $this->get('currency', 'TND');
    }

    public function invoicePrefix(): string
    {
        return $this->get('invoice_prefix', 'FAC');
    }

    public function creditNotePrefix(): string
    {
        return $this->get('credit_note_prefix', 'AV');
    }

    public function paymentPrefix(): string
    {
        return $this->get('payment_prefix', 'PAY');
    }

    public function receiptPrefix(): string
    {
        return $this->get('receipt_prefix', 'REC');
    }

    public function refundPrefix(): string
    {
        return $this->get('refund_prefix', 'REM');
    }

    /**
     * Données du cabinet regroupées pour les documents PDF.
     *
     * @return array<string, string|null>
     */
    public function cabinet(): array
    {
        return [
            'cabinet_name' => $this->name(),
            'cabinet_address' => $this->address(),
            'cabinet_phone' => $this->phone(),
            'cabinet_email' => $this->email(),
            'cabinet_fiscal_number' => $this->fiscalNumber(),
            'cabinet_rib' => $this->rib(),
            'cabinet_logo' => $this->logoPath(),
            'currency' => $this->currency(),
        ];
    }
}
