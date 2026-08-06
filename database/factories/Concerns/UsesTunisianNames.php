<?php

namespace Database\Factories\Concerns;

/**
 * Prénoms et noms tunisiens réalistes partagés entre les factories.
 */
trait UsesTunisianNames
{
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

    protected function randomMaleFirstName(): string
    {
        return $this->faker->randomElement($this->maleFirstNames);
    }

    protected function randomFemaleFirstName(): string
    {
        return $this->faker->randomElement($this->femaleFirstNames);
    }

    protected function randomFirstName(): string
    {
        return $this->faker->randomElement([...$this->maleFirstNames, ...$this->femaleFirstNames]);
    }

    protected function randomLastName(): string
    {
        return $this->faker->randomElement($this->lastNames);
    }

    /**
     * Génère un numéro de téléphone mobile tunisien (+216 8 chiffres).
     */
    protected function tunisianPhone(): string
    {
        $prefix = $this->faker->randomElement([
            20, 21, 22, 23, 24, 25, 26, 27, 28, 29,
            50, 51, 52, 53, 54, 55, 56, 57, 58, 59,
            90, 91, 92, 93, 94, 95, 96, 97, 98, 99,
        ]);

        return '+216'.$prefix.$this->faker->numerify('######');
    }
}
