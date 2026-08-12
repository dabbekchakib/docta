<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            AccountingPlanSeeder::class,
            PatientSeeder::class,
            DoctorSeeder::class,
            SecretarySeeder::class,
            AppointmentSeeder::class,
            ConsultationSeeder::class,
            MedicalRecordSeeder::class,
            PrescriptionSeeder::class,
            LaboratorySeeder::class,
            BillingSeeder::class,
        ]);

        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
            ]
        );
    }
}
