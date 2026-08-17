<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AIActivityLog extends Model
{
    use HasFactory;

    protected $table = 'ai_activity_logs';

    protected $fillable = [
        'user_id',
        'conversation_id',
        'tool_name',
        'request_summary',
        'action_performed',
        'parameters',
        'status',
        'result_summary',
        'executed_at',
    ];

    protected function casts(): array
    {
        return [
            'parameters' => 'array',
            'executed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(AIConversation::class, 'conversation_id');
    }
}
