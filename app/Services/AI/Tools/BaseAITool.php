<?php

namespace App\Services\AI\Tools;

use App\Models\AIActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Log;

abstract class BaseAITool implements AIToolInterface
{
    public function toOpenRouterFormat(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => $this->getName(),
                'description' => $this->getDescription(),
                'parameters' => $this->getParameters(),
            ],
        ];
    }

    public function requiresConfirmation(): bool
    {
        return false;
    }

    public function executeConfirmed(User $user, array $parameters): array
    {
        return $this->execute($user, $parameters);
    }

    public function authorize(User $user): bool
    {
        $permissions = $this->requiredPermissions();

        if (empty($permissions)) {
            return true;
        }

        return $user->hasAnyPermission($permissions);
    }

    /**
     * Enregistre une activité IA dans le journal d'audit.
     */
    protected function logActivity(
        User $user,
        ?int $conversationId,
        string $requestSummary,
        ?string $actionPerformed,
        array $parameters,
        string $status,
        ?string $resultSummary = null,
    ): void {
        AIActivityLog::create([
            'user_id' => $user->id,
            'conversation_id' => $conversationId,
            'tool_name' => $this->getName(),
            'request_summary' => $requestSummary,
            'action_performed' => $actionPerformed,
            'parameters' => $parameters,
            'status' => $status,
            'result_summary' => $resultSummary,
            'executed_at' => now(),
        ]);
    }

    /**
     * Retourne un résultat d'erreur standardisé.
     */
    protected function error(string $message): array
    {
        return [
            'success' => false,
            'error' => $message,
        ];
    }

    /**
     * Retourne un résultat de succès standardisé.
     */
    protected function success(mixed $data, ?string $summary = null): array
    {
        return [
            'success' => true,
            'data' => $data,
            'summary' => $summary,
        ];
    }

    /**
     * Retourne un résultat nécessitant une confirmation.
     */
    protected function needsConfirmation(string $summary, array $data): array
    {
        return [
            'success' => true,
            'requires_confirmation' => true,
            'confirmation_data' => [
                'summary' => $summary,
                'details' => $data,
                'tool' => $this->getName(),
            ],
        ];
    }
}
