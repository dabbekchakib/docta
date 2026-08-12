<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\User;
use App\Notifications\InvoiceIssuedNotification;
use App\Notifications\InvoiceOverdueNotification;
use App\Services\InvoiceService;
use App\Support\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class InvoiceServiceTest extends TestCase
{
    use RefreshDatabase;

    private InvoiceService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        $this->service = app(InvoiceService::class);
    }

    /**
     * @return array{patient: Patient, doctor: Doctor, data: array<string, mixed>, items: array<int, array<string, mixed>>}
     */
    private function basePayload(): array
    {
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();

        return [
            'patient' => $patient,
            'doctor' => $doctor,
            'data' => [
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'invoice_date' => now()->toDateString(),
                'discount_type' => 'none',
                'discount_value' => 0,
            ],
            'items' => [
                ['description' => 'Consultation', 'quantity' => '1', 'unit_price' => '100', 'tax_rate' => '19'],
            ],
        ];
    }

    public function test_create_generates_number_and_draft_status(): void
    {
        $payload = $this->basePayload();

        $invoice = $this->service->create($payload['data'], $payload['items']);

        $this->assertSame('FAC-'.now()->format('Y').'-000001', $invoice->invoice_number);
        $this->assertSame(InvoiceStatus::Draft, $invoice->status);
        $this->assertSame($payload['patient']->id, $invoice->patient_id);
        $this->assertSame($payload['doctor']->id, $invoice->doctor_id);
        $this->assertCount(1, $invoice->items);
        $this->assertSame('119.000', $invoice->total);
        $this->assertSame('119.000', $invoice->amount_remaining);
        $this->assertSame('0.000', $invoice->amount_paid);
    }

    public function test_update_recalculates_totals_and_replaces_items(): void
    {
        $payload = $this->basePayload();
        $invoice = $this->service->create($payload['data'], $payload['items']);

        $invoice = $this->service->update($invoice, $payload['data'], [
            ['description' => 'Analyses', 'quantity' => '2', 'unit_price' => '50', 'tax_rate' => '19'],
        ]);

        $this->assertCount(1, $invoice->items);
        $this->assertSame('Analyses', $invoice->items->first()->description);
        $this->assertSame('119.000', $invoice->total);
        $this->assertSame('119.000', $invoice->amount_remaining);
        $this->assertSame(InvoiceStatus::Draft, $invoice->status);
    }

    public function test_update_rejects_non_draft_invoice(): void
    {
        $payload = $this->basePayload();
        $invoice = $this->service->create($payload['data'], $payload['items']);
        $this->service->issue($invoice);

        $this->expectExceptionMessage('Seule une facture brouillon peut être modifiée.');

        $this->service->update($invoice, $payload['data'], $payload['items']);
    }

    public function test_issue_marks_invoice_issued_and_notifies_patient(): void
    {
        $payload = $this->basePayload();
        $invoice = $this->service->create($payload['data'], $payload['items']);

        $invoice = $this->service->issue($invoice);

        $this->assertSame(InvoiceStatus::Issued, $invoice->status);
        $this->assertNotNull($invoice->issued_at);

        Notification::assertSentTo($payload['patient'], InvoiceIssuedNotification::class);
    }

    public function test_issue_rejects_non_draft_invoice(): void
    {
        $payload = $this->basePayload();
        $invoice = $this->service->create($payload['data'], $payload['items']);
        $this->service->issue($invoice);

        $this->expectExceptionMessage('Seule une facture brouillon peut être émise.');

        $this->service->issue($invoice);
    }

    public function test_cancel_rejects_paid_invoice(): void
    {
        $payload = $this->basePayload();
        $invoice = $this->service->create($payload['data'], $payload['items']);
        $this->service->issue($invoice);
        $invoice->forceFill(['status' => InvoiceStatus::Paid])->save();

        $this->expectExceptionMessage('Une facture payée ne peut pas être annulée ; utilisez un avoir.');

        $this->service->cancel($invoice, 'Erreur');
    }

    public function test_cancel_sets_cancelled_status_with_reason(): void
    {
        $payload = $this->basePayload();
        $invoice = $this->service->create($payload['data'], $payload['items']);

        $invoice = $this->service->cancel($invoice, 'Doublon');

        $this->assertSame(InvoiceStatus::Cancelled, $invoice->status);
        $this->assertSame('Doublon', $invoice->cancelled_reason);
        $this->assertNotNull($invoice->cancelled_at);
    }

    public function test_refresh_payments_marks_invoice_partially_paid(): void
    {
        $payload = $this->basePayload();
        $invoice = $this->service->create($payload['data'], $payload['items']);
        $this->service->issue($invoice);

        \App\Models\Payment::create([
            'payment_number' => 'PAY-TEST',
            'invoice_id' => $invoice->id,
            'patient_id' => $invoice->patient_id,
            'amount' => '40.000',
            'status' => 'completed',
            'payment_date' => now()->toDateString(),
        ]);

        $invoice = $this->service->refreshPayments($invoice->fresh());

        $this->assertSame(InvoiceStatus::PartiallyPaid, $invoice->status);
        $this->assertSame('40.000', $invoice->amount_paid);
        $this->assertSame('79.000', $invoice->amount_remaining);
    }

    public function test_mark_overdue_flags_expired_invoices_and_notifies(): void
    {
        $payload = $this->basePayload();
        $invoice = $this->service->create($payload['data'], $payload['items']);
        $this->service->issue($invoice);
        $invoice->forceFill(['due_date' => now()->subDay()->toDateString()])->save();

        $overdue = Invoice::factory()->create([
            'status' => InvoiceStatus::Issued,
            'due_date' => now()->subDays(2)->toDateString(),
        ]);

        $count = $this->service->markOverdue();

        $this->assertSame(2, $count);
        $this->assertSame(InvoiceStatus::Overdue, $invoice->fresh()->status);
        $this->assertSame(InvoiceStatus::Overdue, $overdue->fresh()->status);

        Notification::assertSentTo($payload['patient'], InvoiceOverdueNotification::class);
        Notification::assertSentTo($overdue->patient, InvoiceOverdueNotification::class);
    }

    public function test_number_is_generated_with_existing_sequence(): void
    {
        Invoice::factory()->create(['invoice_number' => 'FAC-'.now()->format('Y').'-000099']);

        $this->assertSame('FAC-'.now()->format('Y').'-000002', $this->service->generateNumber());
    }

    public function test_money_helpers_behave_as_expected(): void
    {
        $this->assertSame('0.000', Money::zero());
        $this->assertSame('119', Money::add('100', '19'));
        $this->assertSame('100', Money::sub('119', '19'));
        $this->assertSame('0.000', Money::sub('10', '10'));
    }
}
