<?php

namespace App\Models;

use Database\Factories\LaboratoryReportFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class LaboratoryReport extends Model implements HasMedia
{
    /** @use HasFactory<LaboratoryReportFactory> */
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'laboratory_request_id',
        'report_number',
        'report_date',
        'summary',
        'comments',
        'validated_at',
        'validated_by',
    ];

    protected function casts(): array
    {
        return [
            'report_date' => 'date',
            'validated_at' => 'datetime',
        ];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(LaboratoryRequest::class, 'laboratory_request_id');
    }

    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('laboratory_reports')
            ->useDisk('laboratory-reports');
    }
}
