<?php

namespace App\Livewire;

use App\Models\AIConversation;
use App\Models\AIMessage;
use App\Services\AI\AIService;
use App\Services\AI\ToolRegistry;
use App\Services\AI\Tools\ConfirmActionTool;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AIChatBubble extends Component
{
    public bool $isOpen = false;
    public ?int $conversationId = null;
    public string $message = '';
    public bool $isLoading = false;
    public ?array $pendingConfirmation = null;

    /** @var array<int, array<string, mixed>> */
    public array $conversations = [];

    protected AIService $aiService;
    protected ToolRegistry $toolRegistry;

    protected static string $view = 'livewire.a-i-chat-bubble';

    public function boot(): void
    {
        $this->aiService = app(AIService::class);
        $this->toolRegistry = app(ToolRegistry::class);
    }

    public function togglePanel(): void
    {
        $this->isOpen = ! $this->isOpen;

        if ($this->isOpen && empty($this->conversations)) {
            $this->loadConversations();
        }
    }

    public function loadConversations(): void
    {
        $this->conversations = AIConversation::query()
            ->where('user_id', Auth::id())
            ->where('is_active', true)
            ->orderBy('updated_at', 'desc')
            ->limit(20)
            ->get()
            ->toArray();
    }

    public function getConversationMessages(): \Illuminate\Support\Collection
    {
        if (! $this->conversationId) {
            return collect();
        }

        return AIMessage::query()
            ->where('conversation_id', $this->conversationId)
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function newConversation(): void
    {
        $this->conversationId = null;
        $this->message = '';
        $this->pendingConfirmation = null;
    }

    public function selectConversation(int $id): void
    {
        $conversation = AIConversation::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if ($conversation) {
            $this->conversationId = $conversation->id;
            $this->pendingConfirmation = null;
        }
    }

    public function deleteConversation(int $id): void
    {
        AIConversation::where('id', $id)
            ->where('user_id', Auth::id())
            ->update(['is_active' => false]);

        if ($this->conversationId === $id) {
            $this->conversationId = null;
        }

        $this->loadConversations();
    }

    public function sendMessage(): void
    {
        $this->message = trim($this->message);

        if (empty($this->message)) {
            return;
        }

        $user = Auth::user();

        if (! $this->conversationId) {
            $conversation = AIConversation::create([
                'user_id' => $user->id,
            ]);
            $this->conversationId = $conversation->id;
            $this->loadConversations();
        }

        $userMessage = $this->message;
        $this->message = '';
        $this->isLoading = true;

        $allTools = $this->toolRegistry->getOpenRouterToolsForUser($user);
        $conversation = AIConversation::findOrFail($this->conversationId);

        $response = $this->aiService->chat($conversation, $userMessage, $allTools, $user);

        if (! $response['success']) {
            $this->isLoading = false;
            $this->dispatch('ai-bubble-scroll-to-bottom');

            return;
        }

        if (! empty($response['tool_calls'])) {
            $this->handleToolCalls($response['tool_calls'], $allTools, $user, $conversation);
        }

        $this->isLoading = false;
        $this->loadConversations();
        $this->dispatch('ai-bubble-scroll-to-bottom');
    }

    /**
     * @param  array<int, array<string, mixed>>  $toolCalls
     * @param  array<int, array<string, mixed>>  $allTools
     */
    private function handleToolCalls(array $toolCalls, array $allTools, $user, AIConversation $conversation): void
    {
        $toolResults = [];

        foreach ($toolCalls as $toolCall) {
            $functionName = $toolCall['function']['name'] ?? null;
            $toolCallId = $toolCall['id'] ?? null;
            $parameters = json_decode($toolCall['function']['arguments'] ?? '{}', true);

            if (! $functionName || ! $toolCallId) {
                continue;
            }

            $tool = $this->toolRegistry->get($functionName);

            if (! $tool) {
                $toolResults[] = [
                    'tool_call_id' => $toolCallId,
                    'content' => json_encode(['error' => "Outil '{$functionName}' non disponible."]),
                ];
                continue;
            }

            if (! $tool->authorize($user)) {
                $toolResults[] = [
                    'tool_call_id' => $toolCallId,
                    'content' => json_encode(['error' => 'Permission insuffisante pour cet outil.']),
                ];
                continue;
            }

            $result = $tool->execute($user, $parameters);

            if (! empty($result['requires_confirmation'])) {
                $this->pendingConfirmation = array_merge($result['confirmation_data'], [
                    'tool_call_id' => $toolCallId,
                    'tool_parameters' => $parameters,
                ]);

                $toolResults[] = [
                    'tool_call_id' => $toolCallId,
                    'content' => json_encode([
                        'status' => 'confirmation_required',
                        'summary' => $result['confirmation_data']['summary'],
                    ]),
                ];
            } else {
                $toolResults[] = [
                    'tool_call_id' => $toolCallId,
                    'content' => json_encode($result['data'] ?? $result),
                ];
            }
        }

        if (! empty($toolResults)) {
            $this->aiService->processToolResults($conversation, $toolResults, $allTools);
        }
    }

    public function confirmAction(): void
    {
        if (! $this->pendingConfirmation) {
            return;
        }

        $user = Auth::user();
        $toolName = $this->pendingConfirmation['tool'] ?? $this->pendingConfirmation['tool_name'] ?? null;
        $toolParameters = $this->pendingConfirmation['tool_parameters'] ?? $this->pendingConfirmation['details'] ?? [];
        $toolCallId = $this->pendingConfirmation['tool_call_id'] ?? null;

        if (! $toolName) {
            $this->pendingConfirmation = null;

            return;
        }

        $confirmTool = new ConfirmActionTool();
        $result = $confirmTool->executeConfirmedAction(
            $user,
            $toolName,
            $toolParameters,
            $this->toolRegistry,
            $this->conversationId,
        );

        $this->pendingConfirmation = null;

        if ($this->conversationId) {
            AIMessage::create([
                'conversation_id' => $this->conversationId,
                'role' => 'tool',
                'content' => json_encode([
                    'status' => 'confirmed',
                    'result' => $result['data'] ?? $result,
                    'summary' => $result['summary'] ?? null,
                ]),
                'tool_call_id' => $toolCallId,
            ]);

            $conversation = AIConversation::findOrFail($this->conversationId);
            $allTools = $this->toolRegistry->getOpenRouterToolsForUser($user);

            $this->aiService->processToolResults(
                $conversation,
                [[
                    'tool_call_id' => $toolCallId ?? 'confirm',
                    'content' => json_encode($result),
                ]],
                $allTools,
            );
        }

        $this->dispatch('ai-bubble-scroll-to-bottom');
    }

    public function cancelAction(): void
    {
        $this->pendingConfirmation = null;
    }

    public function render()
    {
        return view(static::$view);
    }
}
