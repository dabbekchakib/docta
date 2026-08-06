<?php

namespace Database\Factories;

use App\Enums\Governorate;
use App\Enums\SecretaryGender;
use App\Enums\SecretaryStatus;
use App\Models\Secretary;
use App\Models\User;
use Database\Factories\Concerns\UsesTunisianNames;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Secretary>
 */
class SecretaryFactory extends Factory
{
    use UsesTunisianNames;

    protected $model = Secretary::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $gender = $this->faker->randomElement([SecretaryGender::Male, SecretaryGender::Female]);
        $firstName = $gender === SecretaryGender::Male
            ? $this->randomMaleFirstName()
            : $this->randomFemaleFirstName();

        return [
            'user_id' => User::factory(),
            'first_name' => $firstName,
            'last_name' => $this->randomLastName(),
            'gender' => $gender,
            'birth_date' => $this->faker->dateTimeBetween('-55 years', '-22 years')->format('Y-m-d'),
            'cin' => $this->faker->unique()->numerify('########'),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->boolean(70) ? $this->tunisianPhone() : null,
            'mobile' => $this->tunisianPhone(),
            'address' => $this->faker->boolean(80) ? $this->faker->streetAddress() : null,
            'city' => $this->faker->randomElement([
                'Tunis', 'Sfax', 'Sousse', 'Nabeul', 'Bizerte', 'Ariana', 'Ben Arous',
                'Monastir', 'Kairouan', 'Gabès', 'Médenine', 'Gafsa', 'La Marsa', 'Hammamet',
            ]),
            'governorate' => $this->faker->randomElement(Governorate::cases()),
            'postal_code' => $this->faker->boolean(70) ? $this->faker->numerify('1###') : null,
            'employee_number' => $this->faker->unique()->numberBetween(1000, 99999),
            'hire_date' => $this->faker->dateTimeBetween('-10 years', '-3 months')->format('Y-m-d'),
            'status' => $this->faker->randomElement([
                SecretaryStatus::Active, SecretaryStatus::Active, SecretaryStatus::Active,
                SecretaryStatus::Active, SecretaryStatus::Active, SecretaryStatus::Active,
                SecretaryStatus::Active, SecretaryStatus::Active,
                SecretaryStatus::Inactive, SecretaryStatus::Inactive,
            ]),
            'notes' => $this->faker->boolean(20) ? $this->faker->paragraph(2) : null,
            'created_at' => $this->faker->dateTimeBetween('-2 years', 'now'),
        ];
    }
}
