<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AIConversation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ai_conversations';

    protected $fillable = [
        'user_id',
        'title',
        'context_type',
        'context_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(AIMessage::class, 'conversation_id');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(AIActivityLog::class, 'conversation_id');
    }

    /**
     * Contexte : résout le modèle associé au contexte.
     */
    public function contextModel(): ?Model
    {
        if (! $this->context_type || ! $this->context_id) {
            return null;
        }

        $modelClass = match ($this->context_type) {
            'patient' => Patient::class,
            'appointment' => Appointment::class,
            'consultation' => Consultation::class,
            'invoice' => Invoice::class,
            default => null,
        };

        return $modelClass ? $modelClass::find($this->context_id) : null;
    }

    /**
     * Génère un titre automatique basé sur le premier message.
     */
    public function generateTitle(): string
    {
        $firstUserMessage = $this->messages()
            ->where('role', 'user')
            ->first();

        if (! $firstUserMessage) {
            return 'Nouvelle conversation';
        }

        $title = mb_substr($firstUserMessage->content, 0, 80);

        return mb_strlen($firstUserMessage->content) > 80 ? $title.'…' : $title;
    }
}
