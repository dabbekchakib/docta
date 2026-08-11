<?php

namespace App\Models;

use Database\Factories\SurgicalHistoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SurgicalHistory extends Model
{
    /** @use HasFactory<SurgicalHistoryFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'medical_record_id',
        'procedure_name',
        'hospital',
        'surgeon',
        'performed_at',
        'reason',
        'complications',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'performed_at' => 'date',
        ];
    }

    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }
}
