<?php

namespace App\Models;

use App\Enums\PrescriptionStatus;
use App\Observers\PrescriptionObserver;
use Database\Factories\PrescriptionFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Activity;

#[ObservedBy([PrescriptionObserver::class])]
class Prescription extends Model
{
    /** @use HasFactory<PrescriptionFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'prescription_number',
        'patient_id',
        'doctor_id',
        'consultation_id',
        'prescription_date',
        'status',
        'notes',
        'valid_until',
        'verification_token',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => PrescriptionStatus::class,
            'prescription_date' => 'date',
            'valid_until' => 'date',
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

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PrescriptionItem::class)->orderBy('sort_order');
    }

    /**
     * Dossier médical du patient associé à l'ordonnance.
     */
    public function medicalRecord(): ?MedicalRecord
    {
        return $this->patient?->medicalRecord;
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
     * L'ordonnance est-elle modifiable ?
     */
    public function isEditable(): bool
    {
        return $this->status === PrescriptionStatus::Draft;
    }

    /**
     * Tâche d'expiration : les ordonnances émises dont la date de validité
     * est dépassée passent au statut « Expirée ».
     */
    public static function expireOverdue(): int
    {
        return static::query()
            ->where('status', PrescriptionStatus::Issued->value)
            ->whereDate('valid_until', '<', now()->toDateString())
            ->update(['status' => PrescriptionStatus::Expired->value]);
    }
}
