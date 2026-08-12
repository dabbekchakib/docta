<?php

namespace App\Models;

use App\Enums\ConsultationStatus;
use App\Enums\ConsultationType;
use App\Observers\ConsultationObserver;
use Database\Factories\ConsultationFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Activity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[ObservedBy([ConsultationObserver::class])]
class Consultation extends Model implements HasMedia
{
    /** @use HasFactory<ConsultationFactory> */
    use HasFactory, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'consultation_number',
        'patient_id',
        'doctor_id',
        'appointment_id',
        'created_by',
        'consultation_date',
        'start_time',
        'end_time',
        'type',
        'reason',
        'symptoms',
        'clinical_examination',
        'diagnosis',
        'secondary_diagnoses',
        'medical_notes',
        'treatment_plan',
        'recommendations',
        'follow_up_date',
        'status',
        'started_at',
        'completed_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'consultation_date' => 'date',
            'follow_up_date' => 'date',
            'type' => ConsultationType::class,
            'status' => ConsultationStatus::class,
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
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

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function vitalSign(): HasOne
    {
        return $this->hasOne(VitalSign::class);
    }

    /**
     * Ordonnances établies lors de cette consultation (module Phase 8).
     */
    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    /**
     * Demandes d'examens de laboratoire prescrites lors de cette consultation (module Phase 9).
     */
    public function laboratoryRequests(): HasMany
    {
        return $this->hasMany(LaboratoryRequest::class);
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

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('consultation_documents')
            ->useDisk('public');
    }
}
