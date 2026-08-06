<?php

namespace Database\Factories;

use App\Enums\DoctorGender;
use App\Enums\DoctorStatus;
use App\Enums\Governorate;
use App\Enums\MedicalSpecialty;
use App\Models\Doctor;
use App\Models\User;
use Database\Factories\Concerns\UsesTunisianNames;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Doctor>
 */
class DoctorFactory extends Factory
{
    use UsesTunisianNames;

    protected $model = Doctor::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $gender = $this->faker->randomElement([DoctorGender::Male, DoctorGender::Female]);
        $firstName = $gender === DoctorGender::Male
            ? $this->randomMaleFirstName()
            : $this->randomFemaleFirstName();

        $startWorkingDate = $this->faker->dateTimeBetween('-25 years', '-1 year');

        return [
            'user_id' => User::factory(),
            'first_name' => $firstName,
            'last_name' => $this->randomLastName(),
            'gender' => $gender,
            'birth_date' => $this->faker->dateTimeBetween('-65 years', '-28 years')->format('Y-m-d'),
            'photo' => null,
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->boolean(70) ? $this->tunisianPhone() : null,
            'mobile' => $this->tunisianPhone(),
            'speciality' => $this->weightedSpecialty(),
            'sub_speciality' => $this->faker->boolean(35) ? $this->faker->word() : null,
            'order_number' => $this->faker->unique()->numberBetween(10000, 99999),
            'national_id' => $this->faker->unique()->numerify('########'),
            'address' => $this->faker->boolean(80) ? $this->faker->streetAddress() : null,
            'city' => $this->faker->randomElement([
                'Tunis', 'Sfax', 'Sousse', 'Nabeul', 'Bizerte', 'Ariana', 'Ben Arous',
                'Monastir', 'Kairouan', 'Gabès', 'Médenine', 'Gafsa', 'La Marsa', 'Hammamet',
            ]),
            'governorate' => $this->faker->randomElement(Governorate::cases()),
            'postal_code' => $this->faker->boolean(70) ? $this->faker->numerify('1###') : null,
            'biography' => $this->faker->boolean(50) ? $this->faker->paragraph(3) : null,
            'consultation_fee' => $this->faker->randomElement([30.000, 40.000, 50.000, 60.000, 70.000, 80.000, 100.000]),
            'consultation_duration' => $this->faker->randomElement([15, 20, 30, 45, 60]),
            'start_working_date' => $startWorkingDate->format('Y-m-d'),
            'signature_image' => null,
            'diploma_file' => null,
            'status' => $this->faker->randomElement([
                DoctorStatus::Active, DoctorStatus::Active, DoctorStatus::Active,
                DoctorStatus::Active, DoctorStatus::Active, DoctorStatus::Active,
                DoctorStatus::Active, DoctorStatus::Active,
                DoctorStatus::Inactive, DoctorStatus::Inactive,
            ]),
            'created_at' => $this->faker->dateTimeBetween('-2 years', 'now'),
        ];
    }

    private function weightedSpecialty(): MedicalSpecialty
    {
        $weighted = collect(MedicalSpecialty::cases())
            ->flatMap(
                fn (MedicalSpecialty $specialty): array => array_fill(
                    0,
                    $specialty === MedicalSpecialty::General ? 3 : 1,
                    $specialty
                )
            );

        return $weighted->random();
    }
}
