<?php

namespace App\Models;

use App\Enums\DurationUnit;
use App\Enums\MedicineForm;
use App\Enums\MedicineRoute;
use Database\Factories\PrescriptionItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrescriptionItem extends Model
{
    /** @use HasFactory<PrescriptionItemFactory> */
    use HasFactory;

    protected $fillable = [
        'prescription_id',
        'medicine_name',
        'active_ingredient',
        'dosage',
        'form',
        'route',
        'frequency',
        'duration',
        'duration_unit',
        'quantity',
        'instructions',
        'notes',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'form' => MedicineForm::class,
            'route' => MedicineRoute::class,
            'duration_unit' => DurationUnit::class,
        ];
    }

    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class);
    }
}
