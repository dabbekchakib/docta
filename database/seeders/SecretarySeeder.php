<?php

namespace Database\Seeders;

use App\Models\Secretary;
use Illuminate\Database\Seeder;

class SecretarySeeder extends Seeder
{
    public function run(): void
    {
        Secretary::factory()->count(10)->create();
    }
}
