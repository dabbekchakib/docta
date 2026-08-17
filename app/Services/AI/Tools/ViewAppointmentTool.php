<?php

namespace App\Services\AI\Tools;

use App\Models\Appointment;
use App\Models\User;

class ViewAppointmentTool extends BaseAITool
{
    public function getName(): string
    {
        return 'view_appointments';
    }

    public function getDescription(): string
    {
        return 'Consulte les rendez-vous. Filtrable par date, patient, médecin ou statut.';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'date' => [
                    'type' => 'string',
                    'description' => 'Date au format YYYY-MM-DD',
                ],
                'patient_id' => [
                    'type' => 'integer',
                    'description' => 'ID du patient',
                ],
                'doctor_id' => [
                    'type' => 'integer',
                    'description' => 'ID du médecin',
                ],
                'status' => [
                    'type' => 'string',
                    'description' => 'Statut: pending, confirmed, waiting, in_progress, completed, cancelled, absent',
                ],
                'limit' => [
                    'type' => 'integer',
                    'description' => 'Nombre max de résultats (défaut: 20)',
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
        $query = Appointment::query()
            ->with(['patient', 'doctor']);

        if (! empty($parameters['date'])) {
            $query->whereDate('appointment_date', $parameters['date']);
        }
        if (! empty($parameters['patient_id'])) {
            $query->where('patient_id', $parameters['patient_id']);
        }
        if (! empty($parameters['doctor_id'])) {
            $query->where('doctor_id', $parameters['doctor_id']);
        }
        if (! empty($parameters['status'])) {
            $query->where('status', $parameters['status']);
        }

        $limit = min($parameters['limit'] ?? 20, 50);
        $appointments = $query->orderBy('appointment_date', 'desc')
            ->orderBy('start_time')
            ->limit($limit)
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'appointment_number' => $a->appointment_number,
                'date' => $a->appointment_date?->format('d/m/Y'),
                'start_time' => $a->start_time,
                'end_time' => $a->end_time,
                'patient' => $a->patient ? trim("{$a->patient->first_name} {$a->patient->last_name}") : null,
                'doctor' => $a->doctor ? trim("{$a->doctor->first_name} {$a->doctor->last_name}") : null,
                'status' => $a->status?->label(),
                'type' => $a->type?->label(),
                'reason' => $a->reason,
            ]);

        $this->logActivity(
            $user,
            null,
            'Consultation rendez-vous',
            null,
            $parameters,
            'success',
            $appointments->count().' rendez-vous trouvé(s)',
        );

        return $this->success($appointments->all(), $appointments->count().' rendez-vous trouvé(s).');
    }
}
