<?php

namespace App\Services\AI\Tools;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;

class CreateAppointmentTool extends BaseAITool
{
    public function getName(): string
    {
        return 'create_appointment';
    }

    public function getDescription(): string
    {
        return 'Propose de créer un rendez-vous pour un patient. Nécessite une confirmation de l\'utilisateur avant exécution.';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'patient_id' => [
                    'type' => 'integer',
                    'description' => 'ID du patient',
                ],
                'doctor_id' => [
                    'type' => 'integer',
                    'description' => 'ID du médecin',
                ],
                'date' => [
                    'type' => 'string',
                    'description' => 'Date du rendez-vous (YYYY-MM-DD)',
                ],
                'start_time' => [
                    'type' => 'string',
                    'description' => 'Heure de début (HH:MM)',
                ],
                'duration' => [
                    'type' => 'integer',
                    'description' => 'Durée en minutes (défaut: 30)',
                ],
                'reason' => [
                    'type' => 'string',
                    'description' => 'Motif du rendez-vous',
                ],
                'notes' => [
                    'type' => 'string',
                    'description' => 'Notes supplémentaires',
                ],
            ],
            'required' => ['patient_id', 'doctor_id', 'date', 'start_time'],
        ];
    }

    public function requiredPermissions(): array
    {
        return ['appointments.create'];
    }

    public function requiresConfirmation(): bool
    {
        return true;
    }

    public function execute(User $user, array $parameters): array
    {
        $patient = Patient::find($parameters['patient_id']);
        if (! $patient) {
            return $this->error('Patient non trouvé.');
        }

        $doctor = Doctor::find($parameters['doctor_id']);
        if (! $doctor) {
            return $this->error('Médecin non trouvé.');
        }

        $duration = $parameters['duration'] ?? 30;
        $startTime = \Carbon\Carbon::parse($parameters['start_time']);
        $endTime = $startTime->copy()->addMinutes($duration);

        $summary = "Créer un rendez-vous:\n".
            "- Patient: ".trim("{$patient->first_name} {$patient->last_name}")." ({$patient->patient_number})\n".
            "- Médecin: Dr. ".trim("{$doctor->first_name} {$doctor->last_name}")."\n".
            "- Date: {$parameters['date']}\n".
            "- Heure: {$startTime->format('H:i')} - {$endTime->format('H:i')}\n".
            "- Motif: ".($parameters['reason'] ?? 'Non spécifié');

        return $this->needsConfirmation($summary, [
            'patient_id' => $patient->id,
            'patient_name' => trim("{$patient->first_name} {$patient->last_name}"),
            'doctor_id' => $doctor->id,
            'doctor_name' => trim("{$doctor->first_name} {$doctor->last_name}"),
            'date' => $parameters['date'],
            'start_time' => $startTime->format('H:i'),
            'end_time' => $endTime->format('H:i'),
            'duration' => $duration,
            'reason' => $parameters['reason'] ?? null,
            'notes' => $parameters['notes'] ?? null,
        ]);
    }

    public function executeConfirmed(User $user, array $parameters): array
    {
        try {
            $appointmentService = app(\App\Services\AppointmentService::class);

            $data = [
                'patient_id' => $parameters['patient_id'],
                'doctor_id' => $parameters['doctor_id'],
                'appointment_date' => $parameters['date'],
                'start_time' => $parameters['start_time'],
                'end_time' => $parameters['end_time'],
                'duration' => $parameters['duration'] ?? 30,
                'reason' => $parameters['reason'] ?? null,
                'notes' => $parameters['notes'] ?? null,
            ];

            $appointment = $appointmentService->create($data, $user);

            $this->logActivity(
                $user,
                null,
                "Création rendez-vous confirmée pour patient #{$parameters['patient_id']}",
                "Rendez-vous {$appointment->appointment_number} créé",
                $parameters,
                'success',
                "Rendez-vous {$appointment->appointment_number} créé avec succès",
            );

            return $this->success([
                'appointment_number' => $appointment->appointment_number,
                'id' => $appointment->id,
                'date' => $appointment->appointment_date?->format('d/m/Y'),
                'time' => $appointment->start_time.' - '.$appointment->end_time,
            ], "Rendez-vous {$appointment->appointment_number} créé avec succès.");
        } catch (\Exception $e) {
            $this->logActivity(
                $user,
                null,
                "Échec création rendez-vous",
                null,
                $parameters,
                'error',
                $e->getMessage(),
            );

            return $this->error('Erreur lors de la création: '.$e->getMessage());
        }
    }
}
