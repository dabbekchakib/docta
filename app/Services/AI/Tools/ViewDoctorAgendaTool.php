<?php

namespace App\Services\AI\Tools;

use App\Models\Doctor;
use App\Models\User;

class ViewDoctorAgendaTool extends BaseAITool
{
    public function getName(): string
    {
        return 'view_doctor_agenda';
    }

    public function getDescription(): string
    {
        return 'Consulte l\'agenda d\'un médecin pour une date donnée. Montre les créneaux disponibles et les rendez-vous.';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'doctor_id' => [
                    'type' => 'integer',
                    'description' => 'ID du médecin',
                ],
                'date' => [
                    'type' => 'string',
                    'description' => 'Date au format YYYY-MM-DD (défaut: aujourd\'hui)',
                ],
            ],
        ];
    }

    public function requiredPermissions(): array
    {
        return ['appointments.view', 'appointments.manage'];
    }

    public function execute(User $user, array $parameters): array
    {
        $doctorId = $parameters['doctor_id'] ?? null;
        $date = $parameters['date'] ?? now()->format('Y-m-d');

        $query = Doctor::query();
        if ($doctorId) {
            $query->where('id', $doctorId);
        } else {
            // Si médecin connecté, utiliser son ID
            $doctorUser = Doctor::where('user_id', $user->id)->first();
            if ($doctorUser) {
                $doctorId = $doctorUser->id;
                $query->where('id', $doctorId);
            } else {
                return $this->error('Veuillez spécifier un médecin.');
            }
        }

        $doctor = $query->first();
        if (! $doctor) {
            return $this->error('Médecin non trouvé.');
        }

        // Récupérer les rendez-vous de la journée
        $appointments = $doctor->appointments()
            ->with('patient')
            ->whereDate('appointment_date', $date)
            ->orderBy('start_time')
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'start_time' => $a->start_time,
                'end_time' => $a->end_time,
                'patient' => $a->patient ? trim("{$a->patient->first_name} {$a->patient->last_name}") : null,
                'status' => $a->status?->label(),
                'type' => $a->type?->label(),
            ]);

        // Récupérer les horaires du médecin pour ce jour
        $dayOfWeek = \Carbon\Carbon::parse($date)->dayOfWeek;
        $schedule = $doctor->schedules()
            ->where('day_of_week', $dayOfWeek)
            ->first();

        $data = [
            'doctor' => trim("{$doctor->first_name} {$doctor->last_name}"),
            'speciality' => $doctor->speciality?->label(),
            'date' => $date,
            'schedule' => $schedule ? [
                'start' => $schedule->start_time,
                'end' => $schedule->end_time,
                'break_start' => $schedule->break_start,
                'break_end' => $schedule->break_end,
            ] : 'Horaires non configurés',
            'appointments_count' => $appointments->count(),
            'appointments' => $appointments->all(),
        ];

        $this->logActivity(
            $user,
            null,
            "Agenda médecin #{$doctor->id} le {$date}",
            null,
            $parameters,
            'success',
            $appointments->count().' rendez-vous ce jour',
        );

        return $this->success($data, "Agenda Dr. ".trim("{$doctor->first_name} {$doctor->last_name}")." le {$date}: {$appointments->count()} rendez-vous.");
    }
}
