<?php

namespace App\Models;

use App\Enums\DoctorGender;
use App\Enums\DoctorStatus;
use App\Enums\Governorate;
use App\Enums\MedicalSpecialty;
use App\Observers\DoctorObserver;
use Database\Factories\DoctorFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Activity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[ObservedBy([DoctorObserver::class])]
class Doctor extends Model implements HasMedia
{
    /** @use HasFactory<DoctorFactory> */
    use HasFactory, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'user_id',
        'doctor_code',
        'first_name',
        'last_name',
        'gender',
        'birth_date',
        'photo',
        'email',
        'phone',
        'mobile',
        'speciality',
        'sub_speciality',
        'order_number',
        'national_id',
        'address',
        'city',
        'governorate',
        'postal_code',
        'biography',
        'consultation_fee',
        'consultation_duration',
        'start_working_date',
        'signature_image',
        'diploma_file',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'gender' => DoctorGender::class,
            'status' => DoctorStatus::class,
            'speciality' => MedicalSpecialty::class,
            'governorate' => Governorate::class,
            'birth_date' => 'date',
            'start_working_date' => 'date',
            'consultation_fee' => 'decimal:3',
            'consultation_duration' => 'integer',
        ];
    }

    public function fullName(): Attribute
    {
        return Attribute::get(fn (): string => trim("{$this->first_name} {$this->last_name}"));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Patients suivis par le médecin via les rendez-vous (module Phase 3).
     */
    public function patients(): HasManyThrough
    {
        return $this->hasManyThrough(Patient::class, Appointment::class, 'doctor_id', 'id');
    }

    /**
     * Rendez-vous (module Phase 3).
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Horaires de travail (module Agenda).
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(DoctorSchedule::class);
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
     * Factures (module Phase 5).
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
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
        $this->addMediaCollection('photo')
            ->singleFile()
            ->useDisk('public');

        $this->addMediaCollection('diploma')
            ->singleFile()
            ->useDisk('public');

        $this->addMediaCollection('signature')
            ->singleFile()
            ->useDisk('public');
    }
}
