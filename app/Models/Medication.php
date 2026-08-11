<?php

namespace App\Models;

use App\Enums\MedicationStatus;
use Database\Factories\MedicationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Medication extends Model
{
    /** @use HasFactory<MedicationFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'medical_record_id',
        'name',
        'active_ingredient',
        'dosage',
        'frequency',
        'route',
        'started_at',
        'ended_at',
        'prescriber',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => MedicationStatus::class,
            'started_at' => 'date',
            'ended_at' => 'date',
        ];
    }

    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }
}
