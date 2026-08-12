<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InvoiceItem>
 */
class InvoiceItemFactory extends Factory
{
    protected $model = InvoiceItem::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = $this->faker->randomFloat(3, 1, 3);
        $unitPrice = $this->faker->randomFloat(3, 10, 200);

        return [
            'invoice_id' => Invoice::factory(),
            'service_id' => null,
            'description' => $this->faker->words(3, true),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'discount_percent' => 0,
            'tax_rate' => $this->faker->randomElement([0, 7, 13, 19]),
            'tax_amount' => 0,
            'line_total' => round($quantity * $unitPrice, 3),
            'sort_order' => 0,
        ];
    }

    public function withService(Service $service): static
    {
        return $this->state(fn (): array => [
            'service_id' => $service->id,
            'description' => $service->name,
            'unit_price' => $service->price,
            'tax_rate' => $service->taxRate?->rate ?? 0,
            'line_total' => round($service->price, 3),
        ]);
    }
}
