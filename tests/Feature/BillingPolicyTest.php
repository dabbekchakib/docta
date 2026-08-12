<?php

namespace Tests\Feature;

use App\Enums\CreditNoteStatus;
use App\Enums\InvoiceStatus;
use App\Enums\RefundStatus;
use App\Models\CreditNote;
use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\Refund;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_admin_has_full_billing_access(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $invoice = Invoice::factory()->create(['status' => InvoiceStatus::Draft]);
        $payment = Payment::factory()->create();
        $receipt = Receipt::factory()->create();
        $creditNote = CreditNote::factory()->draft()->create();
        $refund = Refund::factory()->create(['status' => RefundStatus::Pending]);

        $this->assertTrue($admin->can('create', Invoice::class));
        $this->assertTrue($admin->can('update', $invoice));
        $this->assertTrue($admin->can('issue', $invoice));
        $this->assertTrue($admin->can('cancel', $invoice));
        $this->assertTrue($admin->can('download', $invoice));
        $this->assertTrue($admin->can('create', Payment::class));
        $this->assertTrue($admin->can('cancel', $payment));
        $this->assertTrue($admin->can('create', Receipt::class));
        $this->assertTrue($admin->can('download', $receipt));
        $this->assertTrue($admin->can('create', CreditNote::class));
        $this->assertTrue($admin->can('issue', $creditNote));
        $this->assertTrue($admin->can('create', Refund::class));
        $this->assertTrue($admin->can('approve', $refund));
        $this->assertTrue($admin->can('reject', $refund));
    }

    public function test_doctor_can_view_billing_but_not_create(): void
    {
        $doctorUser = User::factory()->create();
        $doctorUser->assignRole('doctor');

        $doctor = Doctor::factory()->create(['user_id' => $doctorUser->id]);
        $ownInvoice = Invoice::factory()->create([
            'doctor_id' => $doctor->id,
            'status' => InvoiceStatus::Issued,
        ]);
        $otherInvoice = Invoice::factory()->create([
            'doctor_id' => Doctor::factory()->create()->id,
            'status' => InvoiceStatus::Issued,
        ]);

        $this->assertTrue($doctorUser->can('viewAny', Invoice::class));
        $this->assertTrue($doctorUser->can('view', $ownInvoice));
        $this->assertFalse($doctorUser->can('view', $otherInvoice));
        $this->assertFalse($doctorUser->can('create', Invoice::class));
        $this->assertFalse($doctorUser->can('update', $ownInvoice));
        $this->assertFalse($doctorUser->can('cancel', $ownInvoice));
        $this->assertFalse($doctorUser->can('create', Payment::class));
        $this->assertFalse($doctorUser->can('create', CreditNote::class));
        $this->assertFalse($doctorUser->can('create', Refund::class));
    }

    public function test_secretary_can_create_invoices_and_payments_but_not_refunds(): void
    {
        $secretary = User::factory()->create();
        $secretary->assignRole('secretary');

        $this->assertTrue($secretary->can('create', Invoice::class));
        $this->assertTrue($secretary->can('issue', Invoice::factory()->create(['status' => InvoiceStatus::Draft])));
        $this->assertTrue($secretary->can('create', Payment::class));
        $this->assertTrue($secretary->can('cancel', Payment::factory()->create()));
        $this->assertTrue($secretary->can('create', CreditNote::class));
        $this->assertFalse($secretary->can('create', Refund::class));
        $this->assertFalse($secretary->can('approve', Refund::factory()->create(['status' => RefundStatus::Pending])));
    }

    public function test_accountant_can_manage_refunds_and_reports(): void
    {
        $accountant = User::factory()->create();
        $accountant->assignRole('accountant');

        $this->assertFalse($accountant->can('create', Invoice::class));
        $this->assertTrue($accountant->can('viewAny', Invoice::class));
        $this->assertTrue($accountant->can('download', Invoice::factory()->create()));
        $this->assertFalse($accountant->can('create', Payment::class));
        $this->assertTrue($accountant->can('viewAny', Payment::class));
        $this->assertTrue($accountant->can('create', Refund::class));
        $this->assertTrue($accountant->can('approve', Refund::factory()->create(['status' => RefundStatus::Pending])));
        $this->assertTrue($accountant->can('reject', Refund::factory()->create(['status' => RefundStatus::Pending])));
        $this->assertTrue($accountant->hasPermissionTo('financial_reports.view'));
        $this->assertTrue($accountant->hasPermissionTo('financial_reports.export'));
    }

    public function test_invoice_issue_is_only_allowed_on_draft(): void
    {
        $secretary = User::factory()->create();
        $secretary->assignRole('secretary');

        $issued = Invoice::factory()->create(['status' => InvoiceStatus::Issued]);

        $this->assertTrue($secretary->can('issue', Invoice::factory()->create(['status' => InvoiceStatus::Draft])));
        $this->assertFalse($secretary->can('issue', $issued));
        $this->assertFalse($secretary->can('update', $issued));
    }

    public function test_invoice_update_is_only_allowed_on_draft(): void
    {
        $secretary = User::factory()->create();
        $secretary->assignRole('secretary');

        $draft = Invoice::factory()->create(['status' => InvoiceStatus::Draft]);

        $this->assertTrue($secretary->can('update', $draft));
        $this->assertFalse($secretary->can('update', Invoice::factory()->create(['status' => InvoiceStatus::Paid])));
    }

    public function test_credit_note_issue_is_only_allowed_on_draft(): void
    {
        $secretary = User::factory()->create();
        $secretary->assignRole('secretary');

        $draft = CreditNote::factory()->draft()->create();
        $issued = CreditNote::factory()->create(['status' => CreditNoteStatus::Issued]);

        $this->assertTrue($secretary->can('issue', $draft));
        $this->assertFalse($secretary->can('issue', $issued));
    }
}
