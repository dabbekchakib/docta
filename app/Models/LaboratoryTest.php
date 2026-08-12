<?php

namespace App\Models;

use App\Enums\SampleType;
use Database\Factories\LaboratoryTestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LaboratoryTest extends Model
{
    /** @use HasFactory<LaboratoryTestFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'test_category_id',
        'name',
        'code',
        'description',
        'sample_type',
        'unit',
        'default_reference_value',
        'is_active',
        'requires_fasting',
        'instructions',
    ];

    protected function casts(): array
    {
        return [
            'sample_type' => SampleType::class,
            'is_active' => 'boolean',
            'requires_fasting' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TestCategory::class, 'test_category_id');
    }

    public function referenceRanges(): HasMany
    {
        return $this->hasMany(ReferenceRange::class);
    }

    public function requestItems(): HasMany
    {
        return $this->hasMany(LaboratoryRequestItem::class);
    }
}
