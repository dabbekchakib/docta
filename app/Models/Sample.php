<?php

namespace App\Models;

use App\Enums\SampleType;
use Database\Factories\SampleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sample extends Model
{
    /** @use HasFactory<SampleFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'laboratory_request_id',
        'laboratory_request_item_id',
        'sample_number',
        'sample_type',
        'collected_at',
        'collected_by',
        'received_at',
        'status',
        'rejection_reason',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'sample_type' => SampleType::class,
            'collected_at' => 'datetime',
            'received_at' => 'datetime',
        ];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(LaboratoryRequest::class, 'laboratory_request_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(LaboratoryRequestItem::class, 'laboratory_request_item_id');
    }

    public function collectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collected_by');
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}
