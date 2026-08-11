<?php

namespace App\Models;

use App\Enums\AlcoholStatus;
use App\Enums\SmokingStatus;
use Database\Factories\LifestyleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lifestyle extends Model
{
    /** @use HasFactory<LifestyleFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'medical_record_id',
        'smoking_status',
        'smoking_quantity',
        'alcohol_status',
        'physical_activity',
        'diet',
        'sleep_quality',
        'occupation_risk',
        'other_risks',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'smoking_status' => SmokingStatus::class,
            'alcohol_status' => AlcoholStatus::class,
        ];
    }

    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }
}
