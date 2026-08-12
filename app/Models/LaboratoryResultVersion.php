<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaboratoryResultVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'laboratory_result_id',
        'previous_value',
        'previous_numeric_value',
        'new_value',
        'new_numeric_value',
        'reason',
        'corrected_by',
        'corrected_at',
    ];

    protected function casts(): array
    {
        return [
            'previous_numeric_value' => 'decimal:3',
            'new_numeric_value' => 'decimal:3',
            'corrected_at' => 'datetime',
        ];
    }

    public function result(): BelongsTo
    {
        return $this->belongsTo(LaboratoryResult::class, 'laboratory_result_id');
    }

    public function correctedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'corrected_by');
    }
}
