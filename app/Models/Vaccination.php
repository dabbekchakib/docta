<?php

namespace App\Models;

use Database\Factories\VaccinationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vaccination extends Model
{
    /** @use HasFactory<VaccinationFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'medical_record_id',
        'vaccine_name',
        'dose_number',
        'administered_at',
        'next_due_at',
        'provider',
        'batch_number',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'dose_number' => 'integer',
            'administered_at' => 'date',
            'next_due_at' => 'date',
        ];
    }

    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }
}
