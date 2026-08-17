<?php

namespace App\Services\AI;

use App\Models\AIConversation;
use App\Models\AIMessage;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIService
{
    private string $apiKey;
    private string $model;
    private string $baseUrl;
    private int $maxTokens;
    private float $temperature;

    public function __construct()
    {
        $this->apiKey = config('ai.openrouter.api_key');
        $this->model = config('ai.openrouter.model');
        $this->baseUrl = config('ai.openrouter.base_url');
        $this->maxTokens = config('ai.openrouter.max_tokens', 4096);
        $this->temperature = config('ai.openrouter.temperature', 0.7);
    }

    /**
     * Envoie un message à OpenRouter avec les tools disponibles et retourne la réponse.
     *
     * @param  array<int, array<string, mixed>>  $tools
     */
    public function chat(
        AIConversation $conversation,
        string $userMessage,
        array $tools = [],
        ?User $user = null,
    ): array {
        // Sauvegarder le message utilisateur
        AIMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => $userMessage,
        ]);

        // Construire les messages pour OpenRouter
        $messages = $this->buildMessages($conversation, $user);

        // Appel à OpenRouter
        $response = $this->callOpenRouter($messages, $tools);

        if (! $response['success']) {
            return [
                'success' => false,
                'error' => $response['error'] ?? 'Erreur lors de la communication avec l\'assistant IA.',
            ];
        }

        $data = $response['data'];

        // Extraire la réponse
        $choice = $data['choices'][0] ?? null;
        if (! $choice) {
            return [
                'success' => false,
                'error' => 'Aucune réponse reçue de l\'assistant IA.',
            ];
        }

        $message = $choice['message'];

        // Sauvegarder la réponse de l'assistant
        $assistantMessage = AIMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'assistant',
            'content' => $message['content'] ?? null,
            'tool_calls' => $message['tool_calls'] ?? null,
            'metadata' => [
                'model' => $data['model'] ?? $this->model,
                'usage' => $data['usage'] ?? null,
            ],
        ]);

        // Mettre à jour le titre si c'est le premier échange
        if ($conversation->messages()->count() <= 2 && ! $conversation->title) {
            $conversation->update(['title' => $conversation->generateTitle()]);
        }

        $result = [
            'success' => true,
            'content' => $message['content'] ?? null,
            'tool_calls' => $message['tool_calls'] ?? null,
            'message_id' => $assistantMessage->id,
        ];

        // Si tool_calls, les retourner pour exécution
        if (! empty($message['tool_calls'])) {
            $result['requires_tool_execution'] = true;
        }

        return $result;
    }

    /**
     * Envoie les résultats des tools à OpenRouter pour obtenir une réponse finale.
     *
     * @param  array<int, array<string, mixed>>  $toolResults
     */
    public function processToolResults(
        AIConversation $conversation,
        array $toolResults,
        array $tools = [],
    ): array {
        // Sauvegarder les résultats des tools comme messages tool
        foreach ($toolResults as $result) {
            AIMessage::create([
                'conversation_id' => $conversation->id,
                'role' => 'tool',
                'content' => is_string($result['content']) ? $result['content'] : json_encode($result['content']),
                'tool_call_id' => $result['tool_call_id'],
            ]);
        }

        // Construire les messages avec les résultats
        $messages = $this->buildMessages($conversation);

        // Appel à OpenRouter
        $response = $this->callOpenRouter($messages, $tools);

        if (! $response['success']) {
            return [
                'success' => false,
                'error' => $response['error'] ?? 'Erreur lors de la communication avec l\'assistant IA.',
            ];
        }

        $data = $response['data'];
        $choice = $data['choices'][0] ?? null;

        if (! $choice) {
            return [
                'success' => false,
                'error' => 'Aucune réponse reçue.',
            ];
        }

        $message = $choice['message'];

        // Sauvegarder
        AIMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'assistant',
            'content' => $message['content'] ?? null,
            'tool_calls' => $message['tool_calls'] ?? null,
            'metadata' => [
                'model' => $data['model'] ?? $this->model,
                'usage' => $data['usage'] ?? null,
            ],
        ]);

        return [
            'success' => true,
            'content' => $message['content'] ?? null,
            'tool_calls' => $message['tool_calls'] ?? null,
            'requires_tool_execution' => ! empty($message['tool_calls']),
        ];
    }

    /**
     * Teste la connexion à OpenRouter.
     */
    public function testConnection(): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->apiKey,
                'HTTP-Referer' => config('app.url'),
                'X-Title' => 'DOCTA Medical ERP',
            ])->timeout(15)->post($this->baseUrl.'/chat/completions', [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'user', 'content' => 'Test de connexion. Réponds "OK".'],
                ],
                'max_tokens' => 10,
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'model' => $response->json('model', 'unknown'),
                    'message' => 'Connexion réussie à OpenRouter.',
                ];
            }

            return [
                'success' => false,
                'error' => 'Erreur HTTP '.$response->status().': '.$response->body(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Impossible de se connecter à OpenRouter: '.$e->getMessage(),
            ];
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $tools
     */
    private function callOpenRouter(array $messages, array $tools = []): array
    {
        try {
            $payload = [
                'model' => $this->model,
                'messages' => $messages,
                'max_tokens' => $this->maxTokens,
                'temperature' => $this->temperature,
            ];

            if (! empty($tools)) {
                $payload['tools'] = $tools;
                $payload['tool_choice'] = 'auto';
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->apiKey,
                'HTTP-Referer' => config('app.url'),
                'X-Title' => 'DOCTA Medical ERP',
                'Content-Type' => 'application/json',
            ])->timeout(60)->post($this->baseUrl.'/chat/completions', $payload);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            Log::error('OpenRouter API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'error' => 'Erreur API OpenRouter (HTTP '.$response->status().').',
            ];
        } catch (\Exception $e) {
            Log::error('OpenRouter connection error', [
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Erreur de connexion: '.$e->getMessage(),
            ];
        }
    }

    /**
     * @param  User|null  $user
     * @return array<int, array<string, mixed>>
     */
    private function buildMessages(AIConversation $conversation, ?User $user = null): array
    {
        $messages = [];

        // System prompt
        $systemPrompt = config('ai.system_prompt');

        // Ajouter le contexte si disponible
        if ($conversation->context_type && $conversation->context_id) {
            $contextModel = $conversation->contextModel();
            if ($contextModel) {
                $contextLabel = config('ai.context_labels.'.$conversation->context_type, $conversation->context_type);
                $systemPrompt .= "\n\nContexte actuel: {$contextLabel} #{$conversation->context_id}";
            }
        }

        // Ajouter les infos de l'utilisateur
        if ($user) {
            $roles = $user->roles->pluck('name')->implode(', ');
            $systemPrompt .= "\n\nUtilisateur connecté: {$user->name} (rôles: {$roles})";
        }

        $messages[] = ['role' => 'system', 'content' => $systemPrompt];

        // Historique de la conversation (derniers 40 messages pour le contexte)
        $history = $conversation->messages()
            ->orderBy('created_at', 'desc')
            ->limit(40)
            ->get()
            ->reverse();

        foreach ($history as $msg) {
            $formatted = ['role' => $msg->role];

            if ($msg->content) {
                $formatted['content'] = $msg->content;
            }

            if ($msg->tool_calls) {
                $formatted['tool_calls'] = $msg->tool_calls;
            }

            if ($msg->tool_call_id) {
                $formatted['tool_call_id'] = $msg->tool_call_id;
            }

            $messages[] = $formatted;
        }

        return $messages;
    }
}
