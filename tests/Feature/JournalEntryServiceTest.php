<?php

namespace Tests\Feature;

use App\Accounting\AccountCode;
use App\Enums\InvoiceStatus;
use App\Enums\JournalEntryStatus;
use App\Enums\JournalEntryType;
use App\Enums\PaymentMethodType;
use App\Enums\PaymentStatus;
use App\Models\AccountingAccount;
use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Services\CreditNoteService;
use App\Services\InvoiceService;
use App\Services\JournalEntryService;
use App\Services\PaymentService;
use App\Services\RefundService;
use Database\Seeders\AccountingPlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class JournalEntryServiceTest extends TestCase
{
    use RefreshDatabase;

    private JournalEntryService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        $this->seed(AccountingPlanSeeder::class);

        $this->service = app(JournalEntryService::class);
    }

    public function test_post_creates_balanced_entry(): void
    {
        $entry = $this->service->post(
            JournalEntryType::Manual->value,
            'Écriture de test',
            [
                ['account_code' => AccountCode::CASH, 'debit' => '100'],
                ['account_code' => AccountCode::REVENUE, 'credit' => '100'],
            ],
        );

        $this->assertSame(JournalEntryStatus::Posted, $entry->status);
        $this->assertCount(2, $entry->lines);
        $this->assertTrue($entry->isBalanced());
        $this->assertStringStartsWith('ECR-'.now()->format('Y').'-', $entry->entry_number);
    }

    public function test_post_rejects_unbalanced_entry(): void
    {
        $this->expectExceptionMessage('Le total des débits doit être égal au total des crédits.');

        $this->service->post('manual', 'Déséquilibrée', [
            ['account_code' => AccountCode::CASH, 'debit' => '100'],
            ['account_code' => AccountCode::REVENUE, 'credit' => '90'],
        ]);
    }

    public function test_post_rejects_inactive_account(): void
    {
        AccountingAccount::factory()->create(['code' => 'X123', 'is_active' => false]);

        $this->expectExceptionMessage('Compte comptable introuvable ou inactif.');

        $this->service->post('manual', 'Compte inactif', [
            ['account_code' => 'X123', 'debit' => '100'],
            ['account_code' => AccountCode::REVENUE, 'credit' => '100'],
        ]);
    }

    public function test_post_rejects_line_with_both_debit_and_credit(): void
    {
        $this->expectExceptionMessage('Une ligne ne peut pas être à la fois au débit et au crédit.');

        $this->service->post('manual', 'Ligne invalide', [
            ['account_code' => AccountCode::CASH, 'debit' => '100', 'credit' => '100'],
        ]);
    }

    public function test_create_update_post_draft_workflow(): void
    {
        $draft = $this->service->createDraft([
            'entry_date' => '2026-08-12',
            'description' => 'Brouillon',
        ], [
            ['account_code' => AccountCode::CASH, 'debit' => '50'],
            ['account_code' => AccountCode::REVENUE, 'credit' => '50'],
        ]);

        $this->assertSame(JournalEntryStatus::Draft, $draft->status);
        $this->assertNull($draft->posted_at);

        $updated = $this->service->updateDraft($draft, [
            'entry_date' => '2026-08-13',
            'description' => 'Brouillon modifié',
        ], [
            ['account_code' => AccountCode::BANK, 'debit' => '75'],
            ['account_code' => AccountCode::REVENUE, 'credit' => '75'],
        ]);

        $this->assertSame('Brouillon modifié', $updated->description);
        $this->assertCount(2, $updated->lines);
        $this->assertTrue($updated->isBalanced());

        $posted = $this->service->postDraft($updated);

        $this->assertSame(JournalEntryStatus::Posted, $posted->status);
        $this->assertNotNull($posted->posted_at);
    }

    public function test_update_draft_rejects_posted_entry(): void
    {
        $posted = $this->service->post('manual', 'Saisie directe', [
            ['account_code' => AccountCode::CASH, 'debit' => '10'],
            ['account_code' => AccountCode::REVENUE, 'credit' => '10'],
        ]);

        $this->expectExceptionMessage('Seul un brouillon d\'écriture peut être modifié.');

        $this->service->updateDraft($posted, [], [
            ['account_code' => AccountCode::CASH, 'debit' => '20'],
            ['account_code' => AccountCode::REVENUE, 'credit' => '20'],
        ]);
    }

    public function test_cancel_only_allows_draft(): void
    {
        $draft = $this->service->createDraft([
            'entry_date' => '2026-08-12',
        ], [
            ['account_code' => AccountCode::CASH, 'debit' => '50'],
            ['account_code' => AccountCode::REVENUE, 'credit' => '50'],
        ]);

        $cancelled = $this->service->cancel($draft, 'Erreur de saisie');

        $this->assertSame(JournalEntryStatus::Cancelled, $cancelled->status);
        $this->assertSame('Erreur de saisie', $cancelled->cancelled_reason);

        $this->expectExceptionMessage('Seule une écriture brouillon peut être annulée.');

        $this->service->cancel($cancelled);
    }

    public function test_invoice_issue_event_generates_entry(): void
    {
        $invoiceService = app(InvoiceService::class);
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();

        $invoice = $invoiceService->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'invoice_date' => '2026-08-12',
        ], [
            ['description' => 'Consultation', 'quantity' => '1', 'unit_price' => '100', 'tax_rate' => '19'],
        ]);

        $invoice = $invoiceService->issue($invoice);

        $entry = JournalEntry::query()
            ->where('source_type', $invoice->getMorphClass())
            ->where('source_id', $invoice->id)
            ->firstOrFail();

        $this->assertSame(JournalEntryType::InvoiceIssue, $entry->type);
        $this->assertSame(JournalEntryStatus::Posted, $entry->status);
        $this->assertCount(3, $entry->lines);
        $this->assertTrue($entry->isBalanced());

        $this->assertSame('119.000', $entry->lines()->whereHas('account', fn ($q) => $q->where('code', AccountCode::RECEIVABLES))->first()->debit);
        $this->assertSame('19.000', $entry->lines()->whereHas('account', fn ($q) => $q->where('code', AccountCode::VAT_COLLECTED))->first()->credit);
        $this->assertSame('100.000', $entry->lines()->whereHas('account', fn ($q) => $q->where('code', AccountCode::REVENUE))->first()->credit);
    }

    public function test_invoice_issue_entry_is_idempotent(): void
    {
        $invoice = Invoice::factory()->create(['total' => '100', 'tax_amount' => '0']);

        $this->service->postInvoiceIssued($invoice);
        $this->service->postInvoiceIssued($invoice);

        $this->assertSame(1, JournalEntry::query()
            ->where('source_type', $invoice->getMorphClass())
            ->where('source_id', $invoice->id)
            ->count());
    }

    public function test_payment_event_generates_entry(): void
    {
        $invoiceService = app(InvoiceService::class);
        $paymentService = app(PaymentService::class);
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();

        $invoice = $invoiceService->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'invoice_date' => '2026-08-12',
        ], [
            ['description' => 'Consultation', 'quantity' => '1', 'unit_price' => '100', 'tax_rate' => '19'],
        ]);
        $invoice = $invoiceService->issue($invoice);

        $method = PaymentMethod::factory()->create(['type' => PaymentMethodType::Cash]);

        $payment = $paymentService->record([
            'invoice_id' => $invoice->id,
            'payment_method_id' => $method->id,
            'amount' => '119',
        ]);

        $entry = JournalEntry::query()
            ->where('source_type', $payment->getMorphClass())
            ->where('source_id', $payment->id)
            ->firstOrFail();

        $this->assertSame(JournalEntryType::Payment, $entry->type);
        $this->assertTrue($entry->isBalanced());

        $cashLine = $entry->lines()->whereHas('account', fn ($q) => $q->where('code', AccountCode::CASH))->first();
        $this->assertSame('119.000', $cashLine->debit);

        $receivableLine = $entry->lines()->whereHas('account', fn ($q) => $q->where('code', AccountCode::RECEIVABLES))->first();
        $this->assertSame('119.000', $receivableLine->credit);
    }

    public function test_credit_note_event_generates_entry(): void
    {
        $creditNoteService = app(CreditNoteService::class);
        $invoice = Invoice::factory()->create([
            'status' => InvoiceStatus::Issued,
            'total' => '200',
            'amount_remaining' => '200',
        ]);

        $creditNote = $creditNoteService->create($invoice, [
            'amount' => '50',
            'reason' => 'Remise commerciale',
        ]);
        $creditNote = $creditNoteService->issue($creditNote);

        $entry = JournalEntry::query()
            ->where('source_type', $creditNote->getMorphClass())
            ->where('source_id', $creditNote->id)
            ->firstOrFail();

        $this->assertSame(JournalEntryType::CreditNote, $entry->type);
        $this->assertTrue($entry->isBalanced());

        $contraLine = $entry->lines()->whereHas('account', fn ($q) => $q->where('code', AccountCode::REVENUE_CONTRA))->first();
        $this->assertSame('50.000', $contraLine->debit);
    }

    public function test_refund_event_generates_entry(): void
    {
        $refundService = app(RefundService::class);
        $payment = Payment::factory()->create([
            'amount' => '100',
            'status' => PaymentStatus::Completed,
        ]);

        $refund = $refundService->request($payment, [
            'amount' => '40',
            'reason' => 'Remboursement de test',
            'refund_method' => 'cash',
        ]);
        $refund = $refundService->approve($refund);
        $refund = $refundService->execute($refund);

        $entry = JournalEntry::query()
            ->where('source_type', $refund->getMorphClass())
            ->where('source_id', $refund->id)
            ->firstOrFail();

        $this->assertSame(JournalEntryType::Refund, $entry->type);
        $this->assertTrue($entry->isBalanced());

        $cashLine = $entry->lines()->whereHas('account', fn ($q) => $q->where('code', AccountCode::CASH))->first();
        $this->assertSame('40.000', $cashLine->credit);
    }

    public function test_cancelling_invoice_contrepasses_entry(): void
    {
        $invoice = Invoice::factory()->create(['total' => '100', 'tax_amount' => '0']);

        $this->service->postInvoiceIssued($invoice);

        $cancelled = $this->service->postInvoiceCancelled($invoice);

        $this->assertSame(JournalEntryStatus::Cancelled, $cancelled->status);
        $this->assertStringContainsString($invoice->invoice_number, $cancelled->cancelled_reason);
    }

    public function test_invoice_cancel_event_generates_entry(): void
    {
        $invoiceService = app(InvoiceService::class);
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();

        $invoice = $invoiceService->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'invoice_date' => '2026-08-12',
        ], [
            ['description' => 'Consultation', 'quantity' => '1', 'unit_price' => '100', 'tax_rate' => '19'],
        ]);
        $invoice = $invoiceService->issue($invoice);

        $invoiceService->cancel($invoice, 'Doublon');

        $entry = JournalEntry::query()
            ->where('source_type', $invoice->getMorphClass())
            ->where('source_id', $invoice->id)
            ->firstOrFail();

        $this->assertSame(JournalEntryStatus::Cancelled, $entry->status);
        $this->assertNotNull($entry->cancelled_at);
    }
}
