<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\PaymentMethod;
use App\Models\Receipt;
use App\Notifications\PaymentReceivedNotification;
use App\Services\InvoiceService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    private PaymentService $service;

    private InvoiceService $invoiceService;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        $this->service = app(PaymentService::class);
        $this->invoiceService = app(InvoiceService::class);
    }

    /**
     * @return array{patient: Patient, invoice: \App\Models\Invoice, method: PaymentMethod}
     */
    private function issuedInvoice(): array
    {
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();
        $method = PaymentMethod::factory()->create();

        $invoice = $this->invoiceService->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'invoice_date' => now()->toDateString(),
        ], [
            ['description' => 'Consultation', 'quantity' => '1', 'unit_price' => '100', 'tax_rate' => '19'],
        ]);

        $this->invoiceService->issue($invoice);

        return ['patient' => $patient, 'invoice' => $invoice, 'method' => $method];
    }

    public function test_record_creates_payment_and_receipt(): void
    {
        $ctx = $this->issuedInvoice();

        $payment = $this->service->record([
            'invoice_id' => $ctx['invoice']->id,
            'payment_method_id' => $ctx['method']->id,
            'amount' => '40',
        ]);

        $this->assertSame('PAY-000001', $payment->payment_number);
        $this->assertSame(PaymentStatus::Completed, $payment->status);
        $this->assertSame('40.000', $payment->amount);

        $receipt = Receipt::where('payment_id', $payment->id)->first();
        $this->assertNotNull($receipt);
        $this->assertSame('REC-000001', $receipt->receipt_number);
        $this->assertSame('40.000', $receipt->amount);

        $invoice = $payment->invoice->fresh();
        $this->assertSame(InvoiceStatus::PartiallyPaid, $invoice->status);
        $this->assertSame('40.000', $invoice->amount_paid);
        $this->assertSame('79.000', $invoice->amount_remaining);

        Notification::assertSentTo($ctx['patient'], PaymentReceivedNotification::class);
    }

    public function test_record_full_payment_marks_invoice_paid(): void
    {
        $ctx = $this->issuedInvoice();

        $payment = $this->service->record([
            'invoice_id' => $ctx['invoice']->id,
            'payment_method_id' => $ctx['method']->id,
            'amount' => '119',
        ]);

        $invoice = $payment->invoice->fresh();

        $this->assertSame(InvoiceStatus::Paid, $invoice->status);
        $this->assertSame('119.000', $invoice->amount_paid);
        $this->assertSame('0.000', $invoice->amount_remaining);
    }

    public function test_record_rejects_amount_above_remaining_balance(): void
    {
        $ctx = $this->issuedInvoice();

        $this->expectExceptionMessage('Le montant dépasse le solde restant dû de la facture.');

        $this->service->record([
            'invoice_id' => $ctx['invoice']->id,
            'payment_method_id' => $ctx['method']->id,
            'amount' => '120',
        ]);
    }

    public function test_record_rejects_non_issued_invoice(): void
    {
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();
        $method = PaymentMethod::factory()->create();

        $invoice = $this->invoiceService->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
        ], [
            ['description' => 'Consultation', 'quantity' => '1', 'unit_price' => '100', 'tax_rate' => '19'],
        ]);

        $this->expectExceptionMessage('Impossible d\'encaisser sur cette facture.');

        $this->service->record([
            'invoice_id' => $invoice->id,
            'payment_method_id' => $method->id,
            'amount' => '50',
        ]);
    }

    public function test_cancel_payment_restores_invoice_balance(): void
    {
        $ctx = $this->issuedInvoice();

        $payment = $this->service->record([
            'invoice_id' => $ctx['invoice']->id,
            'payment_method_id' => $ctx['method']->id,
            'amount' => '40',
        ]);

        $payment = $this->service->cancel($payment, 'Erreur de saisie');

        $this->assertSame(PaymentStatus::Cancelled, $payment->status);
        $this->assertSame('Erreur de saisie', $payment->cancelled_reason);
        $this->assertNull($payment->receipt);

        $invoice = $payment->invoice->fresh();

        $this->assertSame(InvoiceStatus::Issued, $invoice->status);
        $this->assertSame('0.000', $invoice->amount_paid);
        $this->assertSame('119.000', $invoice->amount_remaining);
    }

    public function test_cancel_rejects_cancelled_payment(): void
    {
        $ctx = $this->issuedInvoice();

        $payment = $this->service->record([
            'invoice_id' => $ctx['invoice']->id,
            'payment_method_id' => $ctx['method']->id,
            'amount' => '40',
        ]);

        $this->service->cancel($payment, 'Annulation');

        $this->expectExceptionMessage('Seul un paiement encaissé peut être annulé.');

        $this->service->cancel($payment, 'Double annulation');
    }

    public function test_receipt_number_sequence(): void
    {
        $ctx = $this->issuedInvoice();

        $this->service->record([
            'invoice_id' => $ctx['invoice']->id,
            'payment_method_id' => $ctx['method']->id,
            'amount' => '40',
        ]);

        $this->assertSame('REC-000002', $this->service->generateReceiptNumber());
    }
}
