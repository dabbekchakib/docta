<?php

namespace App\Models;

use App\Enums\AppointmentStatus;
use App\Enums\AppointmentType;
use App\Observers\AppointmentObserver;
use Database\Factories\AppointmentFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Activity;

#[ObservedBy([AppointmentObserver::class])]
class Appointment extends Model
{
    /** @use HasFactory<AppointmentFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'appointment_number',
        'patient_id',
        'doctor_id',
        'secretary_id',
        'created_by',
        'appointment_date',
        'start_time',
        'end_time',
        'duration',
        'status',
        'type',
        'reason',
        'notes',
        'confirmed_at',
        'cancelled_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'appointment_date' => 'date',
            'status' => AppointmentStatus::class,
            'type' => AppointmentType::class,
            'duration' => 'integer',
            'confirmed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function secretary(): BelongsTo
    {
        return $this->belongsTo(Secretary::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Consultation liée au rendez-vous (module Phase 4).
     */
    public function consultation(): HasOne
    {
        return $this->hasOne(Consultation::class);
    }

    /**
     * Journal d'activité.
     *
     * @return MorphMany<Activity, $this>
     */
    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    /**
     * Rendez-vous actifs (non annulés, non supprimés) du médecin sur une date.
     *
     * @param  array{AppointmentStatus}  $statuses
     */
    public function scopeConflicting(Builder $query, int $doctorId, string $date, string $startTime, string $endTime, ?int $ignoreId = null): Builder
    {
        return $query
            ->where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $date)
            ->where(function (Builder $sub) use ($startTime, $endTime): void {
                $sub->whereTime('start_time', '<', $endTime)
                    ->whereTime('end_time', '>', $startTime);
            })
            ->whereIn('status', [
                AppointmentStatus::Pending->value,
                AppointmentStatus::Confirmed->value,
                AppointmentStatus::Waiting->value,
                AppointmentStatus::InProgress->value,
            ])
            ->when($ignoreId, fn (Builder $sub, int $id): Builder => $sub->whereKeyNot($id));
    }
}
