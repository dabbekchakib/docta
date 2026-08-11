<?php

namespace App\Models;

use App\DTO\MedicalAlert;
use App\Enums\BloodGroup;
use App\Enums\MedicalAlertSeverity;
use App\Enums\MedicalAlertType;
use App\Enums\RhFactor;
use App\Observers\MedicalRecordObserver;
use Database\Factories\MedicalRecordFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection as SupportCollection;
use Spatie\Activitylog\Models\Activity;

#[ObservedBy([MedicalRecordObserver::class])]
class MedicalRecord extends Model
{
    /** @use HasFactory<MedicalRecordFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'patient_id',
        'medical_record_number',
        'blood_group',
        'rh_factor',
        'general_notes',
        'emergency_notes',
    ];

    protected function casts(): array
    {
        return [
            'blood_group' => BloodGroup::class,
            'rh_factor' => RhFactor::class,
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function medicalHistories(): HasMany
    {
        return $this->hasMany(MedicalHistory::class);
    }

    public function surgicalHistories(): HasMany
    {
        return $this->hasMany(SurgicalHistory::class);
    }

    public function familyHistories(): HasMany
    {
        return $this->hasMany(FamilyHistory::class);
    }

    public function allergies(): HasMany
    {
        return $this->hasMany(Allergy::class);
    }

    public function chronicDiseases(): HasMany
    {
        return $this->hasMany(ChronicDisease::class);
    }

    public function medications(): HasMany
    {
        return $this->hasMany(Medication::class);
    }

    public function vaccinations(): HasMany
    {
        return $this->hasMany(Vaccination::class);
    }

    public function medicalDocuments(): HasMany
    {
        return $this->hasMany(MedicalDocument::class);
    }

    public function lifestyle(): HasOne
    {
        return $this->hasOne(Lifestyle::class);
    }

    /**
     * Groupe sanguin complet (ex : « A+ »).
     */
    public function fullBloodGroup(): Attribute
    {
        return Attribute::get(function (): ?string {
            $group = $this->blood_group?->value;
            $rh = $this->rh_factor?->value;

            if (! $group) {
                return null;
            }

            if ($rh && $rh !== 'unknown') {
                return $group.$rh;
            }

            return $group;
        });
    }

    /**
     * @return Collection<int, Allergy>
     */
    public function criticalAllergies(): Collection
    {
        return $this->allergies()
            ->where('status', 'active')
            ->whereIn('severity', ['severe', 'critical'])
            ->orderBy('severity')
            ->get();
    }

    /**
     * Alertes médicales actives calculées (allergies critiques, maladies actives).
     *
     * @return Collection<int, MedicalAlert>
     */
    public function alerts(): SupportCollection
    {
        $alerts = collect();

        foreach ($this->criticalAllergies() as $allergy) {
            $alerts->push(new MedicalAlert(
                type: MedicalAlertType::Allergy,
                severity: $allergy->severity === \App\Enums\AllergySeverity::Critical
                    ? MedicalAlertSeverity::Critical
                    : MedicalAlertSeverity::Danger,
                title: 'Alerte allergie',
                message: 'Allergie '.$allergy->severity->getLabel().' : '.$allergy->allergen
                    .($allergy->reaction ? ' — Réaction : '.$allergy->reaction : ''),
            ));
        }

        foreach ($this->activeChronicDiseases() as $disease) {
            $alerts->push(new MedicalAlert(
                type: MedicalAlertType::ChronicDisease,
                severity: MedicalAlertSeverity::Warning,
                title: 'Maladie chronique active',
                message: $disease->disease_name.($disease->icd_code ? ' ('.$disease->icd_code.')' : ''),
            ));
        }

        return $alerts->sortByDesc(fn (MedicalAlert $alert): int => $alert->severity->value === 'critical' ? 2 : ($alert->severity->value === 'danger' ? 1 : 0))->values();
    }

    /**
     * @return Collection<int, ChronicDisease>
     */
    public function activeChronicDiseases(): Collection
    {
        return $this->chronicDiseases()
            ->whereIn('status', ['active', 'controlled'])
            ->orderBy('status')
            ->get();
    }

    /**
     * @return Collection<int, Medication>
     */
    public function activeMedications(): Collection
    {
        return $this->medications()
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
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
