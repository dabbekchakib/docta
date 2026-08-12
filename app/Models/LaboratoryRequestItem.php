<?php

namespace App\Models;

use App\Enums\SampleType;
use Database\Factories\LaboratoryRequestItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LaboratoryRequestItem extends Model
{
    /** @use HasFactory<LaboratoryRequestItemFactory> */
    use HasFactory;

    protected $fillable = [
        'laboratory_request_id',
        'laboratory_test_id',
        'status',
        'sample_type',
        'instructions',
        'notes',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sample_type' => SampleType::class,
        ];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(LaboratoryRequest::class, 'laboratory_request_id');
    }

    public function test(): BelongsTo
    {
        return $this->belongsTo(LaboratoryTest::class, 'laboratory_test_id');
    }

    public function samples(): HasMany
    {
        return $this->hasMany(Sample::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(LaboratoryResult::class);
    }
}
