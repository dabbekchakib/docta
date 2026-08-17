<div
    x-data="{
        open: false,
        toggle() {
            this.open = !this.open;
            $wire.togglePanel();
        }
    }"
    @ai-bubble-open.window="open = true"
    @ai-bubble-scroll-to-bottom.window="$nextTick(() => {
        const c = $refs.bubbleMessages;
        if (c) c.scrollTop = c.scrollHeight;
    })"
    class="fixed bottom-6 end-6 z-[9999]"
>
    {{-- Panneau de chat --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-4 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 scale-95"
        @click.away="open = false"
        x-cloak
        style="position: absolute; bottom: 68px; right: 0; width: 400px; max-width: calc(100vw - 3rem); max-height: min(600px, calc(100vh - 8rem));"
        class="rounded-xl bg-white shadow-2xl ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 overflow-hidden flex flex-col"
    >

        {{-- Header --}}
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-white/10 shrink-0">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-primary-600 dark:bg-primary-500 text-white">
                    <x-heroicon-m-sparkles class="w-4 h-4" />
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-950 dark:text-white">Assistant IA</p>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400">DOCTA</p>
                </div>
            </div>
            <div class="flex items-center gap-1">
                <button
                    wire:click="newConversation"
                    title="Nouvelle conversation"
                    class="fi-btn-icon relative flex items-center justify-center w-8 h-8 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-white/5 dark:text-gray-400 transition-colors cursor-pointer"
                >
                    <x-heroicon-m-plus class="w-4 h-4" />
                </button>
                <button
                    @click="open = false"
                    title="Fermer"
                    class="fi-btn-icon relative flex items-center justify-center w-8 h-8 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-white/5 dark:text-gray-400 transition-colors cursor-pointer"
                >
                    <x-heroicon-m-x-mark class="w-4 h-4" />
                </button>
            </div>
        </div>

        {{-- Zone de messages --}}
        <div
            x-ref="bubbleMessages"
            class="flex-1 overflow-y-auto px-4 py-4 space-y-4 min-h-0"
        >
            {{-- Confirmation --}}
            @if($pendingConfirmation)
                <div class="rounded-xl bg-warning-50 dark:bg-warning-500/10 p-4 ring-1 ring-warning-200 dark:ring-warning-500/20">
                    <div class="flex items-start gap-3">
                        <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-warning-100 dark:bg-warning-500/20 shrink-0">
                            <x-heroicon-m-exclamation-triangle class="w-4 h-4 text-warning-600 dark:text-warning-400" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-warning-700 dark:text-warning-200">Confirmer l'action</p>
                            <p class="mt-1 text-sm leading-5 text-warning-600 dark:text-warning-300/80 whitespace-pre-line">
                                {{ $pendingConfirmation['summary'] ?? 'Action à confirmer' }}
                            </p>
                            <div class="flex items-center gap-2 mt-3">
                                <button wire:click="confirmAction" class="fi-btn fi-btn-size-sm inline-flex items-center justify-center gap-1 rounded-lg bg-primary-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 focus:ring-2 focus:ring-primary-500/50 transition-colors cursor-pointer dark:bg-primary-500 dark:hover:bg-primary-400">
                                    Confirmer
                                </button>
                                <button wire:click="cancelAction" class="fi-btn fi-btn-size-sm inline-flex items-center justify-center gap-1 rounded-lg bg-white px-3 py-1.5 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-gray-300 hover:bg-gray-50 transition-colors cursor-pointer dark:bg-white/5 dark:text-gray-300 dark:ring-white/10 dark:hover:bg-white/10">
                                    Annuler
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            {{-- Messages --}}
            @else
                @php $messages = $this->getConversationMessages(); @endphp

                @forelse($messages as $msg)
                    @if($msg->role === 'user')
                        <div class="flex justify-end">
                            <div class="max-w-[80%] px-3.5 py-2.5 rounded-2xl rounded-br-md bg-gray-100 dark:bg-white/5 text-sm text-gray-900 dark:text-gray-100 whitespace-pre-wrap leading-relaxed">
                                {{ $msg->content }}
                            </div>
                        </div>
                    @elseif($msg->role === 'assistant' && $msg->content)
                        <div class="flex items-start gap-2.5">
                            <div class="flex items-center justify-center w-7 h-7 rounded-full bg-primary-600 dark:bg-primary-500 text-white shrink-0 mt-0.5">
                                <x-heroicon-m-sparkles class="w-3.5 h-3.5" />
                            </div>
                            <div class="flex-1 min-w-0 text-sm leading-relaxed text-gray-700 dark:text-gray-200 whitespace-pre-wrap">
                                {!! nl2br(e($msg->content)) !!}
                            </div>
                        </div>
                    @endif
                @empty
                    <div class="flex flex-col items-center justify-center py-8 text-center">
                        <div class="flex items-center justify-center w-12 h-12 rounded-2xl bg-gray-100 dark:bg-white/5 mb-3">
                            <x-heroicon-m-sparkles class="w-6 h-6 text-gray-400 dark:text-gray-500" />
                        </div>
                        <p class="text-sm font-medium text-gray-950 dark:text-white">Comment puis-je vous aider ?</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Posez une question sur vos données médicales.
                        </p>
                    </div>
                @endforelse

                {{-- Chargement --}}
                @if($isLoading)
                    <div class="flex items-start gap-2.5">
                        <div class="flex items-center justify-center w-7 h-7 rounded-full bg-primary-600 dark:bg-primary-500 text-white shrink-0 mt-0.5">
                            <x-heroicon-m-sparkles class="w-3.5 h-3.5" />
                        </div>
                        <div class="flex items-center gap-1.5 h-7">
                            <span class="w-2 h-2 rounded-full bg-gray-300 dark:bg-white/30 animate-bounce" style="animation-delay:0ms"></span>
                            <span class="w-2 h-2 rounded-full bg-gray-300 dark:bg-white/30 animate-bounce" style="animation-delay:150ms"></span>
                            <span class="w-2 h-2 rounded-full bg-gray-300 dark:bg-white/30 animate-bounce" style="animation-delay:300ms"></span>
                        </div>
                    </div>
                @endif
            @endif
        </div>

        {{-- Zone de saisie --}}
        @if(!$pendingConfirmation)
            <div class="shrink-0 border-t border-gray-200 dark:border-white/10 p-3">
                <div class="flex items-end gap-2">
                    <textarea
                        wire:model.live="message"
                        rows="1"
                        placeholder="Envoyez un message..."
                        x-ref="bubbleInput"
                        @keydown.enter.prevent="if(!$event.shiftKey && !$wire.isLoading) $wire.sendMessage()"
                        x-on:input="$el.style.height = 'auto'; $el.style.height = Math.min($el.scrollHeight, 120) + 'px'"
                        class="flex-1 resize-none rounded-xl bg-gray-100 dark:bg-white/5 px-3.5 py-2.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-white/30 focus:outline-none focus:ring-2 focus:ring-primary-500/50 dark:focus:ring-primary-500/30 leading-relaxed border-0"
                        style="min-height:40px;max-height:120px;"
                    ></textarea>
                    <button
                        wire:click="sendMessage"
                        wire:loading.attr="disabled"
                        :disabled="$wire.message.trim() === ''"
                        class="flex items-center justify-center w-10 h-10 rounded-xl transition-all cursor-pointer shrink-0
                        enabled:bg-primary-600 enabled:text-white enabled:hover:bg-primary-500
                        disabled:bg-gray-200 disabled:text-gray-400 disabled:cursor-not-allowed
                        dark:enabled:bg-primary-500 dark:enabled:text-white dark:enabled:hover:bg-primary-400
                        dark:disabled:bg-white/[0.08] dark:disabled:text-white/20"
                        title="Envoyer"
                    >
                        <x-heroicon-m-arrow-up class="w-5 h-5" />
                    </button>
                </div>
            </div>
        @endif

    </div>

    {{-- Bouton flottant --}}
    <button
        @click="toggle()"
        class="flex items-center justify-center w-14 h-14 rounded-full bg-primary-600 text-white shadow-lg shadow-primary-600/30 hover:bg-primary-500 hover:shadow-xl hover:shadow-primary-600/40 transition-all cursor-pointer dark:bg-primary-500 dark:hover:bg-primary-400"
        :class="{ 'ring-4 ring-primary-600/20 dark:ring-primary-500/20': open }"
        title="Assistant IA"
    >
        <x-heroicon-m-sparkles x-show="!open" class="w-6 h-6" />
        <x-heroicon-m-x-mark x-show="open" class="w-6 h-6" x-cloak />
    </button>

</div>
