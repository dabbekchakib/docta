<?php

namespace App\Models;

use App\Enums\ResultAbnormality;
use Database\Factories\LaboratoryResultFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LaboratoryResult extends Model
{
    /** @use HasFactory<LaboratoryResultFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'laboratory_request_item_id',
        'parameter_name',
        'value',
        'numeric_value',
        'unit',
        'reference_min',
        'reference_max',
        'reference_text',
        'abnormality',
        'comment',
        'resulted_at',
        'validated_at',
        'validated_by',
    ];

    protected function casts(): array
    {
        return [
            'numeric_value' => 'decimal:3',
            'reference_min' => 'decimal:3',
            'reference_max' => 'decimal:3',
            'abnormality' => ResultAbnormality::class,
            'resulted_at' => 'datetime',
            'validated_at' => 'datetime',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(LaboratoryRequestItem::class, 'laboratory_request_item_id');
    }

    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    /**
     * Historique des corrections (traçabilité des modifications).
     *
     * @return HasMany<LaboratoryResultVersion, $this>
     */
    public function versions(): HasMany
    {
        return $this->hasMany(LaboratoryResultVersion::class);
    }

    public function isValidated(): bool
    {
        return $this->validated_at !== null;
    }

    /**
     * Valeur hors intervalle de référence (comparaison numérique uniquement,
     * aucune interprétation médicale).
     */
    public function isOutOfRange(): bool
    {
        if ($this->numeric_value === null || $this->reference_min === null && $this->reference_max === null) {
            return false;
        }

        $numeric = (float) $this->numeric_value;

        if ($this->reference_min !== null && $numeric < (float) $this->reference_min) {
            return true;
        }

        if ($this->reference_max !== null && $numeric > (float) $this->reference_max) {
            return true;
        }

        return false;
    }

    /**
     * Résultat critique (libellé d'anomalie critique uniquement).
     */
    public function isCritical(): bool
    {
        return in_array($this->abnormality, [
            ResultAbnormality::CriticalLow,
            ResultAbnormality::CriticalHigh,
        ], true);
    }
}
