<?php

namespace App\Models;

use App\Enums\MedicalHistoryStatus;
use App\Enums\MedicalHistoryType;
use Database\Factories\MedicalHistoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicalHistory extends Model
{
    /** @use HasFactory<MedicalHistoryFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'medical_record_id',
        'type',
        'title',
        'description',
        'diagnosed_at',
        'resolved_at',
        'status',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'type' => MedicalHistoryType::class,
            'status' => MedicalHistoryStatus::class,
            'diagnosed_at' => 'date',
            'resolved_at' => 'date',
        ];
    }

    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
