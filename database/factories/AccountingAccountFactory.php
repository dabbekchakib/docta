<?php

namespace Database\Factories;

use App\Enums\AccountingAccountType;
use App\Models\AccountingAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AccountingAccount>
 */
class AccountingAccountFactory extends Factory
{
    protected $model = AccountingAccount::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = $this->faker->randomElement(AccountingAccountType::cases());

        return [
            'code' => (string) $this->faker->unique()->numberBetween(100, 8999),
            'name' => $this->faker->words(3, true),
            'type' => $type,
            'category' => $this->faker->randomElement(['tiers', 'financier', 'charges', 'produits']),
            'normal_balance' => $type->normalBalance(),
            'is_system' => false,
            'is_active' => true,
        ];
    }

    public function system(): static
    {
        return $this->state(fn (): array => ['is_system' => true]);
    }
}
