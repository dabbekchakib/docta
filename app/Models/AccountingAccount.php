<?php

namespace App\Models;

use App\Enums\AccountingAccountType;
use Database\Factories\AccountingAccountFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountingAccount extends Model
{
    /** @use HasFactory<AccountingAccountFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'type',
        'category',
        'normal_balance',
        'is_system',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'type' => AccountingAccountType::class,
            'is_system' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function journalEntryLines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function label(): string
    {
        return "{$this->code} — {$this->name}";
    }
}
