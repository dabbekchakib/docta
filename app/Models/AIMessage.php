<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AIMessage extends Model
{
    use HasFactory;

    protected $table = 'ai_messages';

    protected $fillable = [
        'conversation_id',
        'role',
        'content',
        'tool_calls',
        'tool_call_id',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'tool_calls' => 'array',
            'metadata' => 'array',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(AIConversation::class, 'conversation_id');
    }

    /**
     * Formate le message pour l'envoi à OpenRouter.
     */
    public function toOpenRouterFormat(): array
    {
        $message = [
            'role' => $this->role,
        ];

        if ($this->content) {
            $message['content'] = $this->content;
        }

        if ($this->tool_calls) {
            $message['tool_calls'] = $this->tool_calls;
        }

        if ($this->tool_call_id) {
            $message['tool_call_id'] = $this->tool_call_id;
        }

        return $message;
    }
}
