<?php

namespace App\Services\AI\Tools;

use App\Models\User;

/**
 * Tool interne pour gérer la confirmation d'actions par l'utilisateur.
 * Ce tool n'est PAS exposé à OpenRouter - il est géré côté serveur.
 */
class ConfirmActionTool extends BaseAITool
{
    public function getName(): string
    {
        return 'confirm_action';
    }

    public function getDescription(): string
    {
        return 'Exécute une action après confirmation de l\'utilisateur.';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'tool_name' => [
                    'type' => 'string',
                    'description' => 'Nom du tool à exécuter',
                ],
                'parameters' => [
                    'type' => 'object',
                    'description' => 'Paramètres de l\'action',
                ],
                'confirmed' => [
                    'type' => 'boolean',
                    'description' => 'Confirmation de l\'utilisateur',
                ],
            ],
            'required' => ['tool_name', 'confirmed'],
        ];
    }

    public function requiredPermissions(): array
    {
        return [];
    }

    public function authorize(User $user): bool
    {
        return true;
    }

    public function execute(User $user, array $parameters): array
    {
        return $this->error('Ce tool ne peut pas être appelé directement.');
    }

    /**
     * Exécute l'action confirmée via le ToolRegistry.
     */
    public function executeConfirmedAction(
        User $user,
        string $toolName,
        array $toolParameters,
        ToolRegistry $registry,
        ?int $conversationId = null,
    ): array {
        $tool = $registry->get($toolName);

        if (! $tool) {
            return $this->error("Tool '{$toolName}' non trouvé.");
        }

        if (! $tool->authorize($user)) {
            $this->logActivity(
                $user,
                $conversationId,
                "Tentative d'exécution non autorisée: {$toolName}",
                null,
                $toolParameters,
                'denied',
                'Permission refusée',
            );

            return $this->error('Vous n\'avez pas les permissions nécessaires pour cette action.');
        }

        if (! $tool->requiresConfirmation()) {
            return $this->error("Ce tool ({$toolName}) ne nécessite pas de confirmation.");
        }

        return $tool->executeConfirmed($user, $toolParameters);
    }
}
