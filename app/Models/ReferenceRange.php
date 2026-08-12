<?php

namespace App\Models;

use Database\Factories\ReferenceRangeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferenceRange extends Model
{
    /** @use HasFactory<ReferenceRangeFactory> */
    use HasFactory;

    protected $fillable = [
        'laboratory_test_id',
        'gender',
        'age_min',
        'age_max',
        'min_value',
        'max_value',
        'unit',
        'reference_text',
    ];

    protected function casts(): array
    {
        return [
            'age_min' => 'integer',
            'age_max' => 'integer',
            'min_value' => 'decimal:3',
            'max_value' => 'decimal:3',
        ];
    }

    public function test(): BelongsTo
    {
        return $this->belongsTo(LaboratoryTest::class, 'laboratory_test_id');
    }

    public function displayRange(): ?string
    {
        if ($this->reference_text) {
            return $this->reference_text;
        }

        if ($this->min_value !== null && $this->max_value !== null) {
            return "{$this->min_value} – {$this->max_value}".($this->unit ? " {$this->unit}" : '');
        }

        if ($this->min_value !== null) {
            return '≥ '.$this->min_value.($this->unit ? " {$this->unit}" : '');
        }

        if ($this->max_value !== null) {
            return '≤ '.$this->max_value.($this->unit ? " {$this->unit}" : '');
        }

        return null;
    }
}
