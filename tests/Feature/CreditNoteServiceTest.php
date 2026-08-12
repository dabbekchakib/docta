<?php

namespace Tests\Feature;

use App\Enums\CreditNoteStatus;
use App\Enums\InvoiceStatus;
use App\Models\Doctor;
use App\Models\Patient;
use App\Services\CreditNoteService;
use App\Services\InvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreditNoteServiceTest extends TestCase
{
    use RefreshDatabase;

    private CreditNoteService $service;

    private InvoiceService $invoiceService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(CreditNoteService::class);
        $this->invoiceService = app(InvoiceService::class);
    }

    /**
     * @return array{patient: Patient, invoice: \App\Models\Invoice}
     */
    private function issuedInvoice(): array
    {
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();

        $invoice = $this->invoiceService->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'invoice_date' => now()->toDateString(),
        ], [
            ['description' => 'Consultation', 'quantity' => '1', 'unit_price' => '100', 'tax_rate' => '19'],
        ]);

        $this->invoiceService->issue($invoice);

        return ['patient' => $patient, 'invoice' => $invoice];
    }

    public function test_create_generates_number_and_draft_status(): void
    {
        $ctx = $this->issuedInvoice();

        $creditNote = $this->service->create($ctx['invoice'], [
            'amount' => '50',
            'reason' => 'Trop perçu',
        ]);

        $this->assertSame('AV-'.now()->format('Y').'-000001', $creditNote->credit_note_number);
        $this->assertSame(CreditNoteStatus::Draft, $creditNote->status);
        $this->assertSame($ctx['invoice']->id, $creditNote->invoice_id);
        $this->assertSame('50.000', $creditNote->amount);
        $this->assertSame('Trop perçu', $creditNote->reason);
    }

    public function test_create_rejects_draft_invoice(): void
    {
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();

        $invoice = $this->invoiceService->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
        ], [
            ['description' => 'Consultation', 'quantity' => '1', 'unit_price' => '100', 'tax_rate' => '19'],
        ]);

        $this->expectExceptionMessage('Un avoir ne peut être créé que sur une facture émise.');

        $this->service->create($invoice, ['amount' => '50']);
    }

    public function test_create_rejects_amount_over_credit_balance(): void
    {
        $ctx = $this->issuedInvoice();

        $this->expectExceptionMessage('Le montant de l\'avoir dépasse le solde créditable de la facture.');

        $this->service->create($ctx['invoice'], ['amount' => '120']);
    }

    public function test_create_rejects_zero_or_negative_amount(): void
    {
        $ctx = $this->issuedInvoice();

        $this->expectExceptionMessage('Le montant de l\'avoir doit être supérieur à zéro.');

        $this->service->create($ctx['invoice'], ['amount' => '0']);
    }

    public function test_issue_marks_invoice_credited_when_full_amount(): void
    {
        $ctx = $this->issuedInvoice();

        $creditNote = $this->service->create($ctx['invoice'], [
            'amount' => '119',
            'reason' => 'Annulation',
        ]);

        $creditNote = $this->service->issue($creditNote);

        $this->assertSame(CreditNoteStatus::Issued, $creditNote->status);
        $this->assertNotNull($creditNote->issued_at);
        $this->assertSame(InvoiceStatus::Credited, $creditNote->invoice->status);
    }

    public function test_issue_partial_credit_keeps_invoice_issued(): void
    {
        $ctx = $this->issuedInvoice();

        $creditNote = $this->service->create($ctx['invoice'], [
            'amount' => '50',
            'reason' => 'Remise',
        ]);

        $creditNote = $this->service->issue($creditNote);

        $this->assertSame(CreditNoteStatus::Issued, $creditNote->status);
        $this->assertSame(InvoiceStatus::Issued, $creditNote->invoice->status);
    }

    public function test_issue_rejects_non_draft_credit_note(): void
    {
        $ctx = $this->issuedInvoice();

        $creditNote = $this->service->create($ctx['invoice'], ['amount' => '50']);
        $this->service->issue($creditNote);

        $this->expectExceptionMessage('Seul un avoir brouillon peut être émis.');

        $this->service->issue($creditNote->fresh());
    }

    public function test_cancel_credit_note(): void
    {
        $ctx = $this->issuedInvoice();

        $creditNote = $this->service->create($ctx['invoice'], [
            'amount' => '50',
            'reason' => 'Erreur',
        ]);

        $creditNote = $this->service->cancel($creditNote, 'Doublon');

        $this->assertSame(CreditNoteStatus::Cancelled, $creditNote->status);
        $this->assertSame('Doublon', $creditNote->cancelled_reason);
        $this->assertNotNull($creditNote->cancelled_at);
    }

    public function test_credit_balance_is_reduced_by_existing_issued_credit_notes(): void
    {
        $ctx = $this->issuedInvoice();

        $first = $this->service->create($ctx['invoice'], ['amount' => '60']);
        $this->service->issue($first);

        $this->expectExceptionMessage('Le montant de l\'avoir dépasse le solde créditable de la facture.');

        $this->service->create($ctx['invoice'], ['amount' => '60']);
    }
}
