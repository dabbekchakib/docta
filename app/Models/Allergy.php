<?php

namespace App\Models;

use App\Enums\AllergySeverity;
use App\Enums\AllergyStatus;
use App\Enums\AllergyType;
use App\Observers\AllergyObserver;
use Database\Factories\AllergyFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([AllergyObserver::class])]
class Allergy extends Model
{
    /** @use HasFactory<AllergyFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'medical_record_id',
        'allergen',
        'type',
        'reaction',
        'severity',
        'discovered_at',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'type' => AllergyType::class,
            'severity' => AllergySeverity::class,
            'status' => AllergyStatus::class,
            'discovered_at' => 'date',
        ];
    }

    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    /**
     * Allergie active nécessitant une forte visibilité.
     */
    public function isCritical(): bool
    {
        return $this->status === AllergyStatus::Active && $this->severity->isCritical();
    }
}
