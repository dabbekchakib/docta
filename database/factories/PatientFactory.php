<?php

namespace Database\Factories;

use App\Enums\BloodGroup;
use App\Enums\Governorate;
use App\Enums\PatientGender;
use App\Enums\PatientStatus;
use App\Enums\PatientTitle;
use App\Enums\RelationType;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Patient>
 */
class PatientFactory extends Factory
{
    protected $model = Patient::class;

    /**
     * @var array<int, string>
     */
    protected array $maleFirstNames = [
        'Ahmed', 'Mohamed', 'Ali', 'Hédi', 'Karim', 'Sofien', 'Nabil', 'Youssef', 'Sami',
        'Anis', 'Walid', 'Rachid', 'Moncef', 'Taoufik', 'Slim', 'Zied', 'Mehdi', 'Fares',
        'Omar', 'Skander', 'Majdi', 'Hatem', 'Kamel', 'Lassaad', 'Amine', 'Bilel', 'Riadh',
        'Tarek', 'Mourad', 'Chokri', 'Adel', 'Fathi', 'Hammadi', 'Jamel', 'Lotfi',
    ];

    /**
     * @var array<int, string>
     */
    protected array $femaleFirstNames = [
        'Aïcha', 'Fatma', 'Salma', 'Imen', 'Rania', 'Amira', 'Nour', 'Yasmine', 'Sonia',
        'Rym', 'Leila', 'Souad', 'Meriem', 'Asma', 'Hela', 'Mariem', 'Sabrine', 'Chaima',
        'Emna', 'Mouna', 'Donia', 'Ines', 'Olfa', 'Sarra', 'Houda', 'Nesrine', 'Wiem',
        'Rim', 'Samia', 'Monia', 'Zaineb', 'Amani', 'Dorra', 'Khaoula', 'Bessem',
    ];

    /**
     * @var array<int, string>
     */
    protected array $lastNames = [
        'Trabelsi', 'Ben Ali', 'Bouazizi', 'Gharbi', 'Jebali', 'Mansour', 'Khelifi', 'Saidi',
        'Hamdi', 'Ayari', 'Ben Salah', 'Chaabane', 'Dridi', 'Fakhfakh', 'Ghanem', 'Hassine',
        'Jelloul', 'Kacem', 'Marzouki', 'Naceur', 'Oueslati', 'Rahali', 'Slimani', 'Toumi',
        'Zribi', 'Ben Amor', 'Cherif', 'Daoud', 'El Fekih', 'Hammami', 'Bouzid', 'Mejri',
        'Nasri', 'Riahi', 'Sassi', 'Tlili', 'Ammar', 'Belhadj', 'Boussetta', 'Chikhaoui',
        'Driss', 'Frikha', 'Guedira', 'Hamrouni', 'Karkeni', 'Laroussi', 'Mezghanni', 'Najjar',
        'Ouali', 'Saadaoui', 'Triki', 'Baccouche', 'Chebbi', 'Douiri', 'Gaaloul', 'Harbaoui',
    ];

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $gender = $this->faker->randomElement([PatientGender::Male, PatientGender::Female]);
        $firstName = $gender === PatientGender::Male
            ? $this->faker->randomElement($this->maleFirstNames)
            : $this->faker->randomElement($this->femaleFirstNames);

        $birthDate = $this->faker->dateTimeBetween('-85 years', '-18 years');
        $governorate = $this->faker->randomElement(Governorate::cases());
        $hasCnam = $this->faker->boolean(70);
        $hasInsurance = $this->faker->boolean(25);
        $status = $this->faker->randomElement([
            PatientStatus::Active, PatientStatus::Active, PatientStatus::Active, PatientStatus::Active,
            PatientStatus::Active, PatientStatus::Active, PatientStatus::Active,
            PatientStatus::Inactive, PatientStatus::Inactive, PatientStatus::Inactive,
            PatientStatus::Archived, PatientStatus::Archived, PatientStatus::Deceased,
        ]);

        return [
            'title' => $this->faker->randomElement([PatientTitle::Mr, PatientTitle::Mrs, PatientTitle::Dr]),
            'first_name' => $firstName,
            'last_name' => $this->faker->randomElement($this->lastNames),
            'gender' => $gender,
            'birth_date' => $birthDate->format('Y-m-d'),
            'cin' => $this->faker->unique()->numerify('########'),
            'photo' => null,
            'phone' => $this->faker->unique()->numerify('+216'.$this->faker->randomElement(['20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '50', '51', '52', '53', '54', '55', '56', '57', '58', '59', '90', '91', '92', '93', '94', '95', '96', '97', '98', '99']).'######'),
            'phone_secondary' => $this->faker->boolean(30) ? $this->tunisianPhone() : null,
            'email' => $this->faker->boolean(60) ? $this->faker->unique()->safeEmail() : null,
            'address' => $this->faker->boolean(80) ? $this->faker->streetAddress() : null,
            'city' => $this->faker->randomElement([
                'Tunis', 'Sfax', 'Sousse', 'Nabeul', 'Bizerte', 'Ariana', 'Ben Arous',
                'Monastir', 'Kairouan', 'Gabès', 'Médenine', 'Gafsa', 'La Marsa', 'Hammamet',
            ]),
            'governorate' => $governorate,
            'postal_code' => $this->faker->boolean(70) ? $this->faker->numerify('1###') : null,
            'blood_group' => $this->faker->boolean(80) ? $this->faker->randomElement(BloodGroup::cases()) : null,
            'height' => $this->faker->boolean(60) ? $this->faker->numberBetween(140, 200) : null,
            'weight' => $this->faker->boolean(60) ? $this->faker->numberBetween(40, 130) : null,
            'allergies' => $this->faker->boolean(25) ? $this->faker->randomElement([
                'Pénicilline',
                'Arachides',
                'Pollen',
                'Acide acétylsalicylique',
                'Lactose',
                'Latex',
            ]) : null,
            'medical_history' => $this->faker->boolean(30) ? $this->faker->randomElement([
                'Appendicectomie en 2015',
                'Hypertension artérielle',
                'Asthme infantile',
                'Fracture du poignet gauche',
                'Diabète de type 2',
            ]) : null,
            'chronic_diseases' => $this->faker->boolean(35) ? $this->faker->randomElement([
                'Hypertension artérielle',
                'Diabète de type 2',
                'Asthme',
                'Insuffisance rénale chronique',
                'Hypothyroïdie',
            ]) : null,
            'disability' => $this->faker->boolean(8) ? $this->faker->randomElement([
                'Handicap moteur',
                'Handicap auditif',
                'Malvoyant',
            ]) : null,
            'permanent_treatments' => $this->faker->boolean(25) ? $this->faker->randomElement([
                'Metformine 850 mg matin et soir',
                'Amlodipine 5 mg le matin',
                'Levothyrox 50 µg par jour',
                'Inhalateur Ventoline à la demande',
            ]) : null,
            'medical_notes' => $this->faker->boolean(20) ? $this->faker->sentence(12) : null,
            'has_cnam' => $hasCnam,
            'cnam_number' => $hasCnam ? $this->faker->numerify('#########') : null,
            'has_insurance' => $hasInsurance,
            'insurance_number' => $hasInsurance ? $this->faker->numerify('#######') : null,
            'insurance_expires_at' => $hasInsurance ? $this->faker->dateTimeBetween('-1 year', '+2 years')->format('Y-m-d') : null,
            'emergency_contact' => $this->faker->boolean(75) ? $this->faker->randomElement([...$this->maleFirstNames, ...$this->femaleFirstNames]) : null,
            'emergency_relation' => $this->faker->randomElement(RelationType::cases()),
            'emergency_phone' => $this->faker->boolean(75) ? $this->tunisianPhone() : null,
            'emergency_address' => $this->faker->boolean(40) ? $this->faker->address() : null,
            'status' => $status,
            'created_at' => $this->faker->dateTimeBetween('-2 years', 'now'),
        ];
    }

    /**
     * Génère un numéro de téléphone mobile tunisien (+216 8 chiffres).
     */
    private function tunisianPhone(): string
    {
        $prefix = $this->faker->randomElement([
            20, 21, 22, 23, 24, 25, 26, 27, 28, 29,
            50, 51, 52, 53, 54, 55, 56, 57, 58, 59,
            90, 91, 92, 93, 94, 95, 96, 97, 98, 99,
        ]);

        return '+216'.$prefix.$this->faker->numerify('######');
    }
}
