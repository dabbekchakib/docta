<?php

namespace Database\Seeders;

use App\Enums\CreditNoteStatus;
use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethodType;
use App\Enums\ServiceCategory;
use App\Models\Consultation;
use App\Models\CreditNote;
use App\Models\Invoice;
use App\Models\PaymentMethod;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\Service;
use App\Models\Setting;
use App\Models\TaxRate;
use App\Services\InvoiceCalculationService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BillingSeeder extends Seeder
{
    /**
     * Données de base de la facturation + jeu de démonstration cohérent.
     */
    public function run(): void
    {
        $this->seedDefaults();
        $this->seedSettings();
        $this->seedDemoBilling();
    }

    private function seedDefaults(): void
    {
        foreach ([0, 7, 13, 19] as $rate) {
            TaxRate::updateOrCreate(
                ['code' => 'TVA'.$rate],
                ['name' => 'TVA '.$rate.' %', 'rate' => $rate, 'is_active' => true],
            );
        }

        $methods = [
            ['Espèces', 'CASH', PaymentMethodType::Cash],
            ['Carte bancaire', 'CARD', PaymentMethodType::Card],
            ['Chèque', 'CHECK', PaymentMethodType::Check],
            ['Virement', 'TRANSFER', PaymentMethodType::Transfer],
            ['CNAM', 'CNAM', PaymentMethodType::Cnam],
            ['Assurance', 'INSURANCE', PaymentMethodType::Insurance],
        ];

        foreach ($methods as [$name, $code, $type]) {
            PaymentMethod::updateOrCreate(
                ['code' => $code],
                ['name' => $name, 'type' => $type, 'is_active' => true],
            );
        }

        $tva19 = TaxRate::where('code', 'TVA19')->value('id');

        $services = [
            ['CONSULT', 'Consultation générale', ServiceCategory::Consultation, 60],
            ['CONSULT-SPEC', 'Consultation spécialisée', ServiceCategory::Consultation, 90],
            ['CONTROL', 'Contrôle de suivi', ServiceCategory::Consultation, 40],
            ['URS', 'Analyse d\'urine', ServiceCategory::Laboratory, 15],
            ['NFS', 'Numération formule sanguine', ServiceCategory::Laboratory, 25],
            ['GLYC', 'Glycémie', ServiceCategory::Laboratory, 10],
            ['BIOCH', 'Bilan biochimique complet', ServiceCategory::Laboratory, 80],
            ['RADIO-THORAX', 'Radiographie thorax', ServiceCategory::Imaging, 70],
            ['ECHO-ABDO', 'Échographie abdominale', ServiceCategory::Imaging, 120],
            ['ECG', 'Électrocardiogramme', ServiceCategory::Procedure, 45],
            ['PETIT-ACTE', 'Petit acte chirurgical', ServiceCategory::Procedure, 150],
            ['PANSEMENT', 'Pansement', ServiceCategory::Procedure, 15],
            ['INJ-IM', 'Injection intramusculaire', ServiceCategory::Procedure, 10],
            ['HOSP-JOUR', 'Hospitalisation de jour', ServiceCategory::Hospitalization, 250],
        ];

        foreach ($services as [$code, $name, $category, $price]) {
            Service::updateOrCreate(
                ['code' => $code],
                ['name' => $name, 'category' => $category, 'price' => $price, 'tax_rate_id' => $tva19, 'is_active' => true],
            );
        }
    }

    private function seedSettings(): void
    {
        $settings = [
            'cabinet_name' => 'Cabinet Médical',
            'cabinet_address' => 'Avenue de la Liberté, Tunis',
            'cabinet_phone' => '+216 71 000 000',
            'cabinet_email' => 'contact@docta.tn',
            'cabinet_fiscal_number' => '0000000/X/A/000',
            'currency' => 'TND',
            'invoice_prefix' => 'FAC',
            'credit_note_prefix' => 'AV',
            'payment_prefix' => 'PAY',
            'receipt_prefix' => 'REC',
            'refund_prefix' => 'REM',
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }

    private function seedDemoBilling(): void
    {
        $calculation = app(InvoiceCalculationService::class);

        $patients = Patient::query()->limit(10)->get();

        if ($patients->isEmpty()) {
            return;
        }

        foreach ($patients as $patient) {
            $consultation = Consultation::query()
                ->where('patient_id', $patient->id)
                ->latest('consultation_date')
                ->first();

            $items = [];

            $items[] = [
                'service_id' => Service::where('code', 'CONSULT')->value('id'),
                'description' => 'Consultation générale',
                'quantity' => 1,
                'unit_price' => 60,
                'discount_percent' => 0,
                'tax_rate' => 19,
            ];

            if ($this->fakerBoolean(70)) {
                $items[] = [
                    'service_id' => Service::where('code', 'NFS')->value('id'),
                    'description' => 'Numération formule sanguine',
                    'quantity' => 1,
                    'unit_price' => 25,
                    'discount_percent' => 0,
                    'tax_rate' => 19,
                ];
            }

            $calculated = $calculation->calculate($items, 'none', '0');

            $invoice = Invoice::create([
                'invoice_number' => 'FAC-'.now()->format('Y').'-'.str_pad((string) (Invoice::withTrashed()->count() + 1), 6, '0', STR_PAD_LEFT),
                'patient_id' => $patient->id,
                'doctor_id' => $consultation?->doctor_id,
                'consultation_id' => $consultation?->id,
                'appointment_id' => $consultation?->appointment_id,
                'laboratory_request_id' => null,
                'invoice_date' => now()->subDays(random_int(0, 20))->toDateString(),
                'due_date' => now()->addDays(15)->toDateString(),
                'status' => InvoiceStatus::Issued,
                'discount_type' => 'none',
                'discount_value' => 0,
                'subtotal' => $calculated['subtotal'],
                'discount_amount' => $calculated['discount_amount'],
                'taxable_base' => $calculated['taxable_base'],
                'tax_amount' => $calculated['tax_amount'],
                'total' => $calculated['total'],
                'amount_paid' => 0,
                'amount_remaining' => $calculated['total'],
                'currency' => 'TND',
                'notes' => null,
                'issued_at' => now(),
                'created_by' => null,
            ]);

            $invoice->items()->createMany($calculated['items']);

            if ($this->fakerBoolean(60)) {
                $this->recordDemoPayment($invoice, $calculated['total']);
            } elseif ($this->fakerBoolean(25)) {
                $this->createDemoCreditNote($invoice, $calculated['total']);
            }
        }
    }

    private function recordDemoPayment(Invoice $invoice, string $total): void
    {
        $paidAmount = (float) $total;

        if ($this->fakerBoolean(30)) {
            $paidAmount = round($paidAmount / 2, 3);
        }

        $method = PaymentMethod::inRandomOrder()->first();

        $payment = Payment::create([
            'payment_number' => 'PAY-'.str_pad((string) (Payment::withTrashed()->count() + 1), 6, '0', STR_PAD_LEFT),
            'invoice_id' => $invoice->id,
            'patient_id' => $invoice->patient_id,
            'payment_method_id' => $method?->id,
            'payment_date' => $invoice->invoice_date,
            'amount' => $paidAmount,
            'status' => 'completed',
            'reference' => null,
            'notes' => null,
            'received_by' => null,
        ]);

        Receipt::create([
            'receipt_number' => 'REC-'.str_pad((string) (Receipt::count() + 1), 6, '0', STR_PAD_LEFT),
            'payment_id' => $payment->id,
            'invoice_id' => $invoice->id,
            'patient_id' => $invoice->patient_id,
            'receipt_date' => $payment->payment_date,
            'amount' => $paidAmount,
            'notes' => null,
            'created_by' => null,
        ]);

        $remaining = round((float) $total - $paidAmount, 3);

        $invoice->forceFill([
            'amount_paid' => $paidAmount,
            'amount_remaining' => $remaining,
            'status' => $remaining <= 0 ? InvoiceStatus::Paid : InvoiceStatus::PartiallyPaid,
        ])->save();
    }

    private function createDemoCreditNote(Invoice $invoice, string $total): void
    {
        $amount = round((float) $total * 0.25, 3);

        CreditNote::create([
            'credit_note_number' => 'AV-'.now()->format('Y').'-'.str_pad((string) (CreditNote::withTrashed()->count() + 1), 6, '0', STR_PAD_LEFT),
            'invoice_id' => $invoice->id,
            'patient_id' => $invoice->patient_id,
            'credit_note_date' => $invoice->invoice_date,
            'amount' => $amount,
            'reason' => 'Avoir de démonstration',
            'status' => CreditNoteStatus::Issued,
            'issued_at' => now(),
            'created_by' => null,
        ]);
    }

    private function fakerBoolean(int $chance): bool
    {
        return random_int(1, 100) <= $chance;
    }
}
