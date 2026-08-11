<?php

namespace App\Models;

use App\Enums\MedicalDocumentType;
use App\Observers\MedicalDocumentObserver;
use Database\Factories\MedicalDocumentFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\File;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

#[ObservedBy([MedicalDocumentObserver::class])]
class MedicalDocument extends Model implements HasMedia
{
    /** @use HasFactory<MedicalDocumentFactory> */
    use HasFactory, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'medical_record_id',
        'title',
        'document_type',
        'description',
        'document_date',
        'issued_by',
        'is_confidential',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'document_type' => MedicalDocumentType::class,
            'document_date' => 'date',
            'is_confidential' => 'boolean',
        ];
    }

    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function file(): ?Media
    {
        return $this->getFirstMedia('medical_documents');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('medical_documents')
            ->useDisk('medical-documents')
            ->acceptsFile(fn (File $file): bool => in_array($file->mimeType, [
                'application/pdf',
                'image/jpeg',
                'image/png',
            ], true));
    }
}
