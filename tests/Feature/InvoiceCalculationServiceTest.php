<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\TaxRate;
use App\Services\InvoiceCalculationService;
use App\Support\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceCalculationServiceTest extends TestCase
{
    use RefreshDatabase;

    private InvoiceCalculationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(InvoiceCalculationService::class);
    }

    public function test_calculates_single_line_with_tax(): void
    {
        $result = $this->service->calculate([
            ['description' => 'Consultation', 'quantity' => '1', 'unit_price' => '100', 'tax_rate' => '19'],
        ]);

        $this->assertCount(1, $result['items']);
        $this->assertSame('100', $result['items'][0]['unit_price']);
        $this->assertSame('100', $result['taxable_base']);
        $this->assertSame('19', $result['tax_amount']);
        $this->assertSame('119', $result['subtotal']);
        $this->assertSame('119', $result['total']);
        $this->assertSame('0.000', $result['discount_amount']);
    }

    public function test_calculates_quantity_and_multiple_lines(): void
    {
        $result = $this->service->calculate([
            ['description' => 'A', 'quantity' => '2', 'unit_price' => '50', 'tax_rate' => '7'],
            ['description' => 'B', 'quantity' => '3', 'unit_price' => '10', 'tax_rate' => '0'],
        ]);

        $this->assertSame('137', $result['subtotal']);
        $this->assertSame('7', $result['tax_amount']);
        $this->assertSame('137', $result['total']);
    }

    public function test_applies_percent_discount_on_total(): void
    {
        $result = $this->service->calculate(
            [['description' => 'A', 'quantity' => '1', 'unit_price' => '100', 'tax_rate' => '19']],
            'percent',
            '10'
        );

        $this->assertSame('119', $result['subtotal']);
        $this->assertSame('11.9', $result['discount_amount']);
        $this->assertSame('107.1', $result['total']);
    }

    public function test_applies_fixed_amount_discount_capped_at_subtotal(): void
    {
        $result = $this->service->calculate(
            [['description' => 'A', 'quantity' => '1', 'unit_price' => '50', 'tax_rate' => '0']],
            'amount',
            '500'
        );

        $this->assertSame('50', $result['discount_amount']);
        $this->assertSame('0.000', $result['total']);
    }

    public function test_applies_line_discount_percent(): void
    {
        $result = $this->service->calculate([
            ['description' => 'A', 'quantity' => '1', 'unit_price' => '100', 'tax_rate' => '19', 'discount_percent' => '50'],
        ]);

        $this->assertSame('50', $result['taxable_base']);
        $this->assertSame('9.5', $result['tax_amount']);
        $this->assertSame('59.5', $result['total']);
    }

    public function test_fetches_price_and_tax_from_service_catalog(): void
    {
        $tax = TaxRate::factory()->create(['rate' => 19]);
        $service = Service::factory()->create(['price' => 200.000, 'tax_rate_id' => $tax->id]);

        $result = $this->service->calculate([
            ['service_id' => $service->id, 'quantity' => '1'],
        ]);

        $this->assertSame($service->id, $result['items'][0]['service_id']);
        $this->assertSame('200', $result['items'][0]['unit_price']);
        $this->assertSame('19', $result['items'][0]['tax_rate']);
        $this->assertSame('238', $result['total']);
    }

    public function test_rejects_empty_items(): void
    {
        $this->expectExceptionMessage('Une facture doit contenir au moins une ligne.');

        $this->service->calculate([]);
    }

    public function test_assert_valid_deposit_accepts_remaining_balance(): void
    {
        $invoice = \App\Models\Invoice::factory()->create(['total' => 100, 'amount_remaining' => 40]);

        $this->service->assertValidDeposit('40', $invoice);

        $this->addToAssertionCount(1);
    }

    public function test_assert_valid_deposit_rejects_over_remaining_balance(): void
    {
        $invoice = \App\Models\Invoice::factory()->create(['total' => 100, 'amount_remaining' => 40]);

        $this->expectExceptionMessage('Le montant dépasse le solde restant dû de la facture.');

        $this->service->assertValidDeposit('41', $invoice);
    }

    public function test_sum_collection_of_amounts(): void
    {
        $sum = InvoiceCalculationService::sum(collect(['10.5', '20', '5.000']));

        $this->assertSame('35.5', $sum);
        $this->assertSame('0.000', Money::zero());
    }
}
