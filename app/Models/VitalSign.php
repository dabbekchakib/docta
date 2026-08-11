<?php

namespace App\Models;

use App\Observers\VitalSignObserver;
use Database\Factories\VitalSignFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([VitalSignObserver::class])]
class VitalSign extends Model
{
    /** @use HasFactory<VitalSignFactory> */
    use HasFactory;

    protected $fillable = [
        'consultation_id',
        'temperature',
        'weight',
        'height',
        'bmi',
        'blood_pressure',
        'heart_rate',
        'oxygen_saturation',
        'respiratory_rate',
    ];

    protected function casts(): array
    {
        return [
            'temperature' => 'decimal:1',
            'weight' => 'decimal:1',
            'height' => 'decimal:1',
            'bmi' => 'decimal:1',
            'heart_rate' => 'integer',
            'oxygen_saturation' => 'integer',
            'respiratory_rate' => 'integer',
        ];
    }

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }

    /**
     * Calcule l'IMC (kg/m²) à partir du poids et de la taille.
     */
    public static function computeBmi(?float $weight, ?float $height): ?float
    {
        if (! $weight || ! $height || $height <= 0) {
            return null;
        }

        $heightInMeters = $height / 100;

        return round($weight / ($heightInMeters ** 2), 1);
    }
}
