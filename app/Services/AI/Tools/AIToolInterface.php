<?php

namespace App\Services\AI\Tools;

use App\Models\User;

interface AIToolInterface
{
    /**
     * Nom unique du tool (utilisé par OpenRouter).
     */
    public function getName(): string;

    /**
     * Description du tool (pour le LLM).
     */
    public function getDescription(): string;

    /**
     * Schéma des paramètres JSON (OpenRouter format).
     *
     * @return array<string, mixed>
     */
    public function getParameters(): array;

    /**
     * Retourne le format OpenRouter tool complet.
     *
     * @return array<string, mixed>
     */
    public function toOpenRouterFormat(): array;

    /**
     * Permission(s) Laravel requise(s) pour exécuter ce tool.
     *
     * @return array<int, string>
     */
    public function requiredPermissions(): array;

    /**
     * Vérifie si l'utilisateur a les permissions nécessaires.
     */
    public function authorize(User $user): bool;

    /**
     * Exécute le tool avec les paramètres validés.
     *
     * @param  array<string, mixed>  $parameters
     * @return array{success: bool, data?: mixed, error?: string, requires_confirmation?: bool, confirmation_data?: mixed}
     */
    public function execute(User $user, array $parameters): array;

    /**
     * Indique si ce tool nécessite une confirmation avant exécution (écriture).
     */
    public function requiresConfirmation(): bool;

    /**
     * Exécute l'action réelle après confirmation.
     *
     * @param  array<string, mixed>  $parameters
     * @return array{success: bool, data?: mixed, error?: string}
     */
    public function executeConfirmed(User $user, array $parameters): array;
}
