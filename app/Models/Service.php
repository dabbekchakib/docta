<?php

namespace App\Models;

use App\Enums\ServiceCategory;
use Database\Factories\ServiceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    /** @use HasFactory<ServiceFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'category',
        'price',
        'description',
        'tax_rate_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'category' => ServiceCategory::class,
            'price' => 'decimal:3',
            'is_active' => 'boolean',
        ];
    }

    public function taxRate(): BelongsTo
    {
        return $this->belongsTo(TaxRate::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function priceLabel(): string
    {
        return number_format((float) $this->price, 3, ',', ' ').' DT';
    }
}
