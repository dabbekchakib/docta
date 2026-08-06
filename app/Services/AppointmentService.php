<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Enums\DoctorStatus;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\User;
use App\Notifications\AppointmentNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AppointmentService
{
    /**
     * Génère le numéro de rendez-vous unique (RDV-000001).
     */
    public function generateAppointmentNumber(): string
    {
        $sequence = Appointment::withTrashed()->max('id') ?? 0;

        return 'RDV-'.str_pad((string) ($sequence + 1), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Vérifie que le médecin est disponible sur le créneau demandé.
     *
     * @param  int|Doctor  $doctor
     */
    public function isDoctorAvailable(
        int|Doctor $doctor,
        string $date,
        string $startTime,
        string $endTime,
        ?int $ignoreId = null,
    ): bool {
        $doctorId = $doctor instanceof Doctor ? $doctor->getKey() : $doctor;

        $doctor = $doctor instanceof Doctor ? $doctor : Doctor::find($doctorId);

        if (! $doctor || $doctor->status !== DoctorStatus::Active) {
            return false;
        }

        $start = Carbon::parse($startTime);
        $end = Carbon::parse($endTime);

        if ($end <= $start) {
            return false;
        }

        $conflicting = Appointment::query()
            ->conflicting($doctorId, $date, $start->format('H:i'), $end->format('H:i'), $ignoreId)
            ->exists();

        if ($conflicting) {
            return false;
        }

        return $this->isWithinDoctorSchedule($doctor, $date, $startTime, $endTime);
    }

    /**
     * Vérifie que le créneau est compatible avec les horaires du médecin,
     * uniquement si des horaires ont été configurés.
     */
    public function isWithinDoctorSchedule(Doctor $doctor, string $date, string $startTime, string $endTime): bool
    {
        $schedule = $doctor->schedules()
            ->where('day_of_week', Carbon::parse($date)->dayOfWeek)
            ->first();

        if (! $schedule) {
            return true;
        }

        $start = Carbon::parse($startTime);
        $end = Carbon::parse($endTime);

        if ($start->lt(Carbon::parse($schedule->start_time)) || $end->gt(Carbon::parse($schedule->end_time))) {
            return false;
        }

        if ($schedule->break_start && $schedule->break_end) {
            $breakStart = Carbon::parse($schedule->break_start);
            $breakEnd = Carbon::parse($schedule->break_end);

            if ($start->lt($breakEnd) && $end->gt($breakStart)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Normalise le couple horaire : durée / heure de fin / durée.
     *
     * @param  array<string, mixed>  $data
     * @return array{start_time: string, end_time: string, duration: int}
     */
    public function resolveTimeWindow(array $data): array
    {
        $startTime = $data['start_time'] ?? null;
        $endTime = $data['end_time'] ?? null;
        $duration = (int) ($data['duration'] ?? 30);

        if ($startTime) {
            $start = Carbon::parse($startTime);

            if (! $endTime) {
                $end = $start->copy()->addMinutes($duration);
                $endTime = $end->format('H:i');
            }
        }

        if ($startTime && $endTime) {
            $duration = max(1, (int) $start->diffInMinutes(Carbon::parse($endTime)));
        }

        return [
            'start_time' => (string) $startTime,
            'end_time' => (string) $endTime,
            'duration' => $duration,
        ];
    }

    /**
     * Crée un rendez-vous en vérifiant la disponibilité du médecin.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, ?User $createdBy = null): Appointment
    {
        $this->assertAvailable($data, null);

        $appointment = Appointment::create([
            ...$data,
            'created_by' => $createdBy?->id ?? Auth::id(),
        ]);

        return $appointment;
    }

    /**
     * Met à jour un rendez-vous en vérifiant la disponibilité du médecin.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Appointment $appointment, array $data): Appointment
    {
        $this->assertAvailable($data, $appointment->getKey());

        $appointment->update($data);

        return $appointment;
    }

    /**
     * Change le statut d'un rendez-vous (confirmation, annulation, etc.).
     */
    public function changeStatus(Appointment $appointment, AppointmentStatus $status, ?string $reason = null): Appointment
    {
        $timestamps = match ($status) {
            AppointmentStatus::Confirmed => ['confirmed_at' => now()],
            AppointmentStatus::Cancelled => ['cancelled_at' => now()],
            AppointmentStatus::Completed => ['completed_at' => now()],
            default => [],
        };

        $appointment->fill(['status' => $status, ...$timestamps]);
        $appointment->saveQuietly();

        $this->log($appointment, "Rendez-vous {$status->label()}");

        self::notifyStatusChange($appointment);

        return $appointment;
    }

    public function confirm(Appointment $appointment): Appointment
    {
        return $this->changeStatus($appointment, AppointmentStatus::Confirmed);
    }

    public function cancel(Appointment $appointment, ?string $reason = null): Appointment
    {
        return $this->changeStatus($appointment, AppointmentStatus::Cancelled, $reason);
    }

    public function complete(Appointment $appointment): Appointment
    {
        return $this->changeStatus($appointment, AppointmentStatus::Completed);
    }

    /**
     * Envoie les notifications liées à un changement de statut.
     */
    public static function notifyStatusChange(Appointment $appointment): void
    {
        $status = $appointment->status instanceof AppointmentStatus
            ? $appointment->status
            : AppointmentStatus::from($appointment->status);

        if (in_array($status, [AppointmentStatus::Confirmed, AppointmentStatus::Cancelled, AppointmentStatus::Absent], true)) {
            $appointment->patient?->notify(new AppointmentNotification($appointment, 'status'));
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function assertAvailable(array $data, ?int $ignoreId): void
    {
        $doctorId = $data['doctor_id'] ?? null;
        $date = $data['appointment_date'] ?? null;
        $startTime = $data['start_time'] ?? null;
        $endTime = $data['end_time'] ?? null;

        if (! $doctorId || ! $date || ! $startTime) {
            return;
        }

        $window = $this->resolveTimeWindow($data);

        if (! $this->isDoctorAvailable($doctorId, $date, $window['start_time'], $window['end_time'], $ignoreId)) {
            throw ValidationException::withMessages([
                'doctor_id' => 'Le médecin est déjà occupé sur ce créneau horaire.',
            ]);
        }
    }

    private function log(Appointment $appointment, string $description): void
    {
        activity('appointments')
            ->performedOn($appointment)
            ->causedBy(Auth::user())
            ->log($description);
    }
}
