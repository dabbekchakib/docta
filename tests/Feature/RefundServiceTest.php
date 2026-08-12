<?php

namespace Tests\Feature;

use App\Enums\PaymentStatus;
use App\Enums\RefundStatus;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\PaymentMethod;
use App\Notifications\RefundApprovedNotification;
use App\Notifications\RefundRejectedNotification;
use App\Services\InvoiceService;
use App\Services\PaymentService;
use App\Services\RefundService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RefundServiceTest extends TestCase
{
    use RefreshDatabase;

    private RefundService $service;

    private InvoiceService $invoiceService;

    private PaymentService $paymentService;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        $this->service = app(RefundService::class);
        $this->invoiceService = app(InvoiceService::class);
        $this->paymentService = app(PaymentService::class);
    }

    /**
     * @return array{patient: Patient, payment: \App\Models\Payment}
     */
    private function paidInvoice(): array
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

        $this->invoiceService->issue($invoice);

        $payment = $this->paymentService->record([
            'invoice_id' => $invoice->id,
            'payment_method_id' => $method->id,
            'amount' => '119',
        ]);

        return ['patient' => $patient, 'payment' => $payment];
    }

    public function test_request_creates_pending_refund(): void
    {
        $ctx = $this->paidInvoice();

        $refund = $this->service->request($ctx['payment'], [
            'amount' => '50',
            'reason' => 'Annulation de consultation',
            'refund_method' => 'bank_transfer',
        ]);

        $this->assertSame('REM-000001', $refund->refund_number);
        $this->assertSame(RefundStatus::Pending, $refund->status);
        $this->assertSame('50.000', $refund->amount);
        $this->assertSame($ctx['payment']->id, $refund->payment_id);
        $this->assertSame($ctx['patient']->id, $refund->patient_id);
        $this->assertNotNull($refund->requested_at);
    }

    public function test_request_rejects_amount_over_payment(): void
    {
        $ctx = $this->paidInvoice();

        $this->expectExceptionMessage('Le montant du remboursement doit être supérieur à zéro.');

        $this->service->request($ctx['payment'], ['amount' => '0']);
    }

    public function test_request_rejects_amount_exceeding_payment(): void
    {
        $ctx = $this->paidInvoice();

        $this->expectExceptionMessage('Le remboursement dépasse le montant du paiement.');

        $this->service->request($ctx['payment'], ['amount' => '120']);
    }

    public function test_request_rejects_non_completed_payment(): void
    {
        $ctx = $this->paidInvoice();
        $this->paymentService->cancel($ctx['payment'], 'Annulation');

        $this->expectExceptionMessage('Un remboursement ne peut porter que sur un paiement encaissé.');

        $this->service->request($ctx['payment'], ['amount' => '50']);
    }

    public function test_approve_marks_refund_approved_and_notifies(): void
    {
        $ctx = $this->paidInvoice();
        $refund = $this->service->request($ctx['payment'], ['amount' => '50']);

        $refund = $this->service->approve($refund);

        $this->assertSame(RefundStatus::Approved, $refund->status);
        $this->assertNotNull($refund->approved_at);

        Notification::assertSentTo($ctx['patient'], RefundApprovedNotification::class);
    }

    public function test_reject_marks_refund_rejected_and_notifies(): void
    {
        $ctx = $this->paidInvoice();
        $refund = $this->service->request($ctx['payment'], ['amount' => '50']);

        $refund = $this->service->reject($refund, 'Pièces manquantes');

        $this->assertSame(RefundStatus::Rejected, $refund->status);
        $this->assertSame('Pièces manquantes', $refund->rejected_reason);
        $this->assertNotNull($refund->rejected_at);

        Notification::assertSentTo($ctx['patient'], RefundRejectedNotification::class);
    }

    public function test_execute_full_refund_marks_payment_refunded(): void
    {
        $ctx = $this->paidInvoice();
        $refund = $this->service->request($ctx['payment'], ['amount' => '119']);
        $refund = $this->service->approve($refund);

        $refund = $this->service->execute($refund);

        $this->assertSame(RefundStatus::Completed, $refund->status);
        $this->assertNotNull($refund->completed_at);
        $this->assertSame(PaymentStatus::Refunded, $refund->payment->status);
    }

    public function test_execute_partial_refund_keeps_payment_completed(): void
    {
        $ctx = $this->paidInvoice();
        $refund = $this->service->request($ctx['payment'], ['amount' => '50']);
        $refund = $this->service->approve($refund);

        $refund = $this->service->execute($refund);

        $this->assertSame(RefundStatus::Completed, $refund->status);
        $this->assertSame(PaymentStatus::Completed, $refund->payment->status);
    }

    public function test_refundable_balance_is_reduced_after_refund(): void
    {
        $ctx = $this->paidInvoice();

        $this->assertSame('119', $this->service->refundableBalance($ctx['payment']));

        $refund = $this->service->request($ctx['payment'], ['amount' => '50']);
        $refund = $this->service->approve($refund);
        $this->service->execute($refund);

        $this->assertSame('69', $this->service->refundableBalance($ctx['payment']->fresh()));
    }

    public function test_request_rejects_amount_over_refundable_balance(): void
    {
        $ctx = $this->paidInvoice();

        $refund = $this->service->request($ctx['payment'], ['amount' => '80']);
        $refund = $this->service->approve($refund);
        $this->service->execute($refund);

        $this->expectExceptionMessage('Le montant dépasse le solde remboursable de ce paiement.');

        $this->service->request($ctx['payment'], ['amount' => '50']);
    }

    public function test_cancel_rejects_completed_refund(): void
    {
        $ctx = $this->paidInvoice();

        $refund = $this->service->request($ctx['payment'], ['amount' => '50']);
        $refund = $this->service->approve($refund);
        $this->service->execute($refund);

        $this->expectExceptionMessage('Ce remboursement est déjà clôturé.');

        $this->service->cancel($refund, 'Annulation tardive');
    }

    public function test_approve_rejects_non_pending_refund(): void
    {
        $ctx = $this->paidInvoice();

        $refund = $this->service->request($ctx['payment'], ['amount' => '50']);
        $this->service->reject($refund, 'Refus');

        $this->expectExceptionMessage('Seule une demande en attente peut être approuvée.');

        $this->service->approve($refund->fresh());
    }
}
