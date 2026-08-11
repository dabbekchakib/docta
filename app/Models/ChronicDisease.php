<?php

namespace App\Models;

use App\Enums\AllergySeverity;
use App\Enums\ChronicDiseaseStatus;
use App\Observers\ChronicDiseaseObserver;
use Database\Factories\ChronicDiseaseFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([ChronicDiseaseObserver::class])]
class ChronicDisease extends Model
{
    /** @use HasFactory<ChronicDiseaseFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'medical_record_id',
        'disease_name',
        'icd_code',
        'diagnosed_at',
        'status',
        'severity',
        'treatment',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => ChronicDiseaseStatus::class,
            'severity' => AllergySeverity::class,
            'diagnosed_at' => 'date',
        ];
    }

    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }
}
