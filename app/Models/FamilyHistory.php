<?php

namespace App\Models;

use App\Enums\MedicalHistoryStatus;
use App\Enums\RelativeType;
use Database\Factories\FamilyHistoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FamilyHistory extends Model
{
    /** @use HasFactory<FamilyHistoryFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'medical_record_id',
        'relative',
        'condition',
        'description',
        'diagnosed_at',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'relative' => RelativeType::class,
            'status' => MedicalHistoryStatus::class,
            'diagnosed_at' => 'date',
        ];
    }

    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }
}
