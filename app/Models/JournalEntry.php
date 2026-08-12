<?php

namespace App\Models;

use App\Enums\JournalEntryStatus;
use App\Enums\JournalEntryType;
use Database\Factories\JournalEntryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use App\Support\Money;

class JournalEntry extends Model
{
    /** @use HasFactory<JournalEntryFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'entry_number',
        'entry_date',
        'type',
        'description',
        'source_type',
        'source_id',
        'status',
        'posted_at',
        'cancelled_at',
        'cancelled_reason',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
            'type' => JournalEntryType::class,
            'status' => JournalEntryStatus::class,
            'posted_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function lines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isBalanced(): bool
    {
        return Money::compare((string) $this->lines()->sum('debit'), (string) $this->lines()->sum('credit')) === 0;
    }

    /**
     * @return Collection<int, JournalEntryLine>
     */
    public function postedLines(): Collection
    {
        return $this->lines()->with('account')->orderBy('id')->get();
    }
}
