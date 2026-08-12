<?php

namespace App\Models;

use App\Enums\LaboratoryRequestPriority;
use App\Enums\LaboratoryRequestStatus;
use Database\Factories\LaboratoryRequestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Activity;

class LaboratoryRequest extends Model
{
    /** @use HasFactory<LaboratoryRequestFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'request_number',
        'patient_id',
        'doctor_id',
        'consultation_id',
        'laboratory_id',
        'requested_at',
        'priority',
        'status',
        'clinical_information',
        'doctor_notes',
        'patient_instructions',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'requested_at' => 'date',
            'priority' => LaboratoryRequestPriority::class,
            'status' => LaboratoryRequestStatus::class,
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

    public function laboratory(): BelongsTo
    {
        return $this->belongsTo(Laboratory::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(LaboratoryRequestItem::class)->orderBy('sort_order');
    }

    public function samples(): HasMany
    {
        return $this->hasMany(Sample::class);
    }

    public function report(): HasOne
    {
        return $this->hasOne(LaboratoryReport::class);
    }

    /**
     * Résultats de tous les examens de la demande.
     */
    public function results(): HasManyThrough
    {
        return $this->hasManyThrough(LaboratoryResult::class, LaboratoryRequestItem::class);
    }

    /**
     * Dossier médical du patient associé à la demande.
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
     * La demande est-elle encore modifiable (brouillon uniquement) ?
     */
    public function isEditable(): bool
    {
        return $this->status === LaboratoryRequestStatus::Draft;
    }

    /**
     * Des résultats ont-ils déjà été saisis ?
     */
    public function hasEnteredResults(): bool
    {
        return $this->results()->exists();
    }

    /**
     * Tous les examens de la demande disposent-ils d'au moins un résultat ?
     */
    public function allItemsHaveResults(): bool
    {
        return $this->items()->count() > 0
            && $this->items()->count() === $this->items()
                ->whereHas('results')
                ->count();
    }

    /**
     * La demande est-elle validée biologiquement ?
     */
    public function isValidated(): bool
    {
        return in_array($this->status, [
            LaboratoryRequestStatus::Validated,
            LaboratoryRequestStatus::Completed,
        ], true);
    }
}
