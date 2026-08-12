<?php

namespace App\Models;

use App\Enums\BloodGroup;
use App\Enums\Governorate;
use App\Enums\PatientGender;
use App\Enums\PatientStatus;
use App\Enums\PatientTitle;
use App\Observers\PatientObserver;
use Database\Factories\PatientFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Models\Activity;

#[ObservedBy([PatientObserver::class])]
class Patient extends Model
{
    /** @use HasFactory<PatientFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'patient_number',
        'title',
        'first_name',
        'last_name',
        'gender',
        'birth_date',
        'cin',
        'photo',
        'phone',
        'phone_secondary',
        'email',
        'address',
        'city',
        'governorate',
        'postal_code',
        'blood_group',
        'height',
        'weight',
        'allergies',
        'medical_history',
        'chronic_diseases',
        'disability',
        'permanent_treatments',
        'medical_notes',
        'has_cnam',
        'cnam_number',
        'has_insurance',
        'insurance_number',
        'insurance_expires_at',
        'emergency_contact',
        'emergency_relation',
        'emergency_phone',
        'emergency_address',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'title' => PatientTitle::class,
            'gender' => PatientGender::class,
            'status' => PatientStatus::class,
            'birth_date' => 'date',
            'blood_group' => BloodGroup::class,
            'governorate' => Governorate::class,
            'height' => 'float',
            'weight' => 'float',
            'has_cnam' => 'boolean',
            'has_insurance' => 'boolean',
            'insurance_expires_at' => 'date',
        ];
    }

    public function fullName(): Attribute
    {
        return Attribute::get(fn (): string => trim("{$this->first_name} {$this->last_name}"));
    }

    public function age(): Attribute
    {
        return Attribute::get(fn (): ?int => $this->birth_date?->age);
    }

    /**
     * Rendez-vous (module Phase 3).
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Consultations (module Phase 4).
     */
    public function consultations(): HasMany
    {
        return $this->hasMany(Consultation::class);
    }

    /**
     * Ordonnances (module Phase 4).
     */
    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    /**
     * Demandes d'examens de laboratoire (module Phase 9).
     */
    public function laboratoryRequests(): HasMany
    {
        return $this->hasMany(LaboratoryRequest::class);
    }

    /**
     * Factures (module Phase 5).
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Paiements (module Phase 5).
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Reçus (module Phase 5).
     */
    public function receipts(): HasMany
    {
        return $this->hasMany(Receipt::class);
    }

    /**
     * Avoirs (module Phase 5).
     */
    public function creditNotes(): HasMany
    {
        return $this->hasMany(CreditNote::class);
    }

    /**
     * Remboursements (module Phase 5).
     */
    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    /**
     * Dossier médical (module Phase 4).
     */
    public function medicalRecord(): HasOne
    {
        return $this->hasOne(MedicalRecord::class);
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
}
