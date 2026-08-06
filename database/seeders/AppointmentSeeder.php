<?php

namespace Database\Seeders;

use App\Enums\DoctorStatus;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Patient;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        $doctors = Doctor::query()
            ->where('status', DoctorStatus::Active->value)
            ->orderBy('id')
            ->get();

        if ($doctors->isEmpty()) {
            $doctors = Doctor::factory()->count(6)->create();
        }

        $this->createSchedules($doctors);

        $patients = Patient::query()->orderBy('id')->get();

        if ($patients->isEmpty()) {
            $patients = Patient::factory()->count(60)->create();
        }

        $startTimes = ['08:00', '08:30', '09:00', '09:30', '10:00', '10:30', '11:00', '11:30', '14:00', '14:30', '15:00', '15:30', '16:00', '16:30'];

        for ($i = 0; $i < 100; $i++) {
            Appointment::factory()->create([
                'doctor_id' => $doctors->random()->id,
                'patient_id' => $patients->random()->id,
                'appointment_date' => now()->addDays(rand(-30, 60))->format('Y-m-d'),
                'start_time' => $startTimes[array_rand($startTimes)],
                'duration' => [15, 20, 30, 30, 45, 60][array_rand([0, 1, 2, 2, 3, 4])],
            ]);
        }
    }

    private function createSchedules($doctors): void
    {
        $days = [0, 1, 2, 3, 4];

        foreach ($doctors as $doctor) {
            DoctorSchedule::query()->where('doctor_id', $doctor->id)->delete();

            foreach ($days as $day) {
                DoctorSchedule::create([
                    'doctor_id' => $doctor->id,
                    'day_of_week' => $day,
                    'start_time' => '08:00',
                    'end_time' => '17:00',
                    'break_start' => '12:00',
                    'break_end' => '13:00',
                ]);
            }
        }
    }
}
