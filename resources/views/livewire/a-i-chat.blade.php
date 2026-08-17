<div
    x-data="aiChat()"
    x-init="$nextTick(() => scrollToBottom())"
    @scroll-to-bottom-js.window="$nextTick(() => scrollToBottom())"
    class="flex h-full w-full overflow-hidden bg-gray-50 dark:bg-gray-950 text-gray-950 dark:text-white"
>

    {{-- Mobile overlay backdrop --}}
    <div
        x-show="sidebarOpen && isMobile"
        x-transition:enter="transition-opacity ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="sidebarOpen = false"
        class="fixed inset-0 z-40 bg-gray-950/50 backdrop-blur-sm lg:hidden"
        x-cloak
    ></div>

    {{-- Sidebar --}}
    <aside
        x-show="sidebarOpen || !isMobile"
        x-transition:enter="transition-transform ease-out duration-300"
        x-transition:enter-start="-translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition-transform ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full"
        class="flex flex-col w-[280px] h-full bg-white dark:bg-gray-900 border-e border-gray-200 dark:border-white/10 shrink-0 z-50
               lg:relative lg:translate-x-0"
        :class="{ 'fixed inset-y-0 start-0': isMobile }"
    >

        {{-- Header sidebar --}}
        <div class="flex items-center justify-between h-16 px-4 border-b border-gray-200 dark:border-white/10 shrink-0">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-primary-500 text-white">
                    <x-heroicon-m-sparkles class="w-4 h-4" />
                </div>
                <span class="text-sm font-bold text-gray-950 dark:text-white">
                    Assistant IA
                </span>
            </div>
            <button
                @click="sidebarOpen = false"
                class="flex items-center justify-center w-8 h-8 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-white/5 dark:text-gray-400 transition-colors cursor-pointer lg:hidden"
            >
                <x-heroicon-m-x-mark class="w-5 h-5" />
            </button>
        </div>

        {{-- Bouton nouveau chat --}}
        <div class="p-3">
            <button
                wire:click="newConversation"
                class="group flex items-center gap-3 w-full px-3 py-2.5 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-white/5 transition-colors cursor-pointer"
            >
                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 dark:bg-white/5 group-hover:bg-gray-200 dark:group-hover:bg-white/10 transition-colors">
                    <x-heroicon-m-plus class="w-4 h-4" />
                </div>
                <span>Nouveau chat</span>
            </button>
        </div>

        {{-- Titre historique --}}
        <div class="px-5 pb-2">
            <span class="text-[11px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">
                Historique
            </span>
        </div>

        {{-- Liste des conversations --}}
        <nav class="flex-1 overflow-y-auto px-3 pb-3 space-y-0.5">
            @forelse($conversations as $conversation)
                <div
                    wire:click="selectConversation({{ $conversation['id'] }})"
                    class="group relative flex items-center gap-2.5 px-3 py-2.5 rounded-xl cursor-pointer transition-colors
                    {{ ($conversation['id'] ?? null) == $conversationId
                        ? 'bg-gray-100 dark:bg-white/5 text-gray-950 dark:text-white'
                        : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-white/[0.03]' }}"
                >
                    <x-heroicon-m-chat-bubble-left class="w-[17px] h-[17px] shrink-0 opacity-60" />

                    <span class="flex-1 truncate text-[13px] font-medium">
                        {{ $conversation['title'] ?? 'Nouveau chat' }}
                    </span>

                    <button
                        wire:click.stop="deleteConversation({{ $conversation['id'] }})"
                        wire:confirm="Supprimer cette conversation ?"
                        class="hidden group-hover:flex items-center justify-center w-7 h-7 rounded-lg hover:bg-gray-200 dark:hover:bg-white/10 cursor-pointer"
                    >
                        <x-heroicon-m-trash class="w-3.5 h-3.5 text-gray-400" />
                    </button>
                </div>
            @empty
                <div class="px-3 py-10 text-center">
                    <x-heroicon-m-chat-bubble-left class="w-8 h-8 mx-auto text-gray-300 dark:text-gray-600 mb-2" />
                    <p class="text-xs text-gray-400 dark:text-gray-500">
                        Aucune conversation
                    </p>
                </div>
            @endforelse
        </nav>

        {{-- Utilisateur --}}
        <div class="border-t border-gray-200 dark:border-white/10 p-3">
            <div class="flex items-center gap-3 px-3 py-2.5 rounded-xl">
                <div class="flex items-center justify-center w-8 h-8 rounded-full bg-primary-500 text-white text-xs font-bold shrink-0">
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                        {{ auth()->user()->name ?? 'Utilisateur' }}
                    </p>
                </div>
            </div>
        </div>

    </aside>

    {{-- Zone principale --}}
    <main class="flex flex-col flex-1 min-w-0 h-full">

        {{-- Header --}}
        <header class="flex items-center h-16 px-4 md:px-6 shrink-0 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-white/10">

            <div class="flex items-center gap-2 flex-1 min-w-0">

                {{-- Toggle sidebar --}}
                <button
                    @click="sidebarOpen = !sidebarOpen"
                    class="flex items-center justify-center w-9 h-9 rounded-xl text-gray-500 hover:bg-gray-100 dark:hover:bg-white/5 dark:text-gray-400 transition-colors cursor-pointer"
                    :title="sidebarOpen ? 'Masquer le panneau' : 'Afficher le panneau'"
                >
                    <x-heroicon-m-bars-3 x-show="!sidebarOpen" class="w-5 h-5" />
                    <x-heroicon-m-x-mark x-show="sidebarOpen" class="w-5 h-5" x-cloak />
                </button>

                <h1 class="text-base font-bold text-gray-950 dark:text-white truncate">
                    Assistant IA
                </h1>

                @if($contextType && $contextId)
                    <span class="hidden sm:inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold text-primary-700 bg-primary-50 dark:text-primary-400 dark:bg-primary-500/10">
                        {{ $contextType }} #{{ $contextId }}
                    </span>
                @endif

            </div>

            <div class="flex items-center gap-1">
                <button
                    wire:click="newConversation"
                    title="Nouveau chat"
                    class="flex items-center justify-center w-9 h-9 rounded-xl text-gray-500 hover:bg-gray-100 dark:hover:bg-white/5 dark:text-gray-400 transition-colors cursor-pointer"
                >
                    <x-heroicon-m-pencil-square class="w-[18px] h-[18px]" />
                </button>
            </div>

        </header>

        {{-- Zone de messages --}}
        <div
            x-ref="messagesContainer"
            class="flex-1 overflow-y-auto scroll-smooth"
        >

            {{-- Confirmation en attente --}}
            @if($pendingConfirmation)
                <div class="flex items-center justify-center min-h-full px-4 py-10">
                    <div class="w-full max-w-2xl rounded-2xl border border-amber-200 dark:border-amber-500/20 bg-amber-50 dark:bg-amber-500/5 p-6">
                        <div class="flex items-start gap-4">
                            <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-500/10 shrink-0">
                                <x-heroicon-m-exclamation-triangle class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-bold text-amber-900 dark:text-amber-200">
                                    Confirmer l'action
                                </p>
                                <p class="mt-2 text-sm leading-6 text-amber-800 dark:text-amber-300/80 whitespace-pre-line">
                                    {{ $pendingConfirmation['summary'] ?? 'Action à confirmer' }}
                                </p>
                                <div class="flex items-center gap-2 mt-5">
                                    <button
                                        wire:click="confirmAction"
                                        class="px-4 py-2 rounded-xl bg-primary-600 text-white text-sm font-semibold hover:bg-primary-500 transition-colors cursor-pointer"
                                    >
                                        Confirmer
                                    </button>
                                    <button
                                        wire:click="cancelAction"
                                        class="px-4 py-2 rounded-xl text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5 transition-colors cursor-pointer"
                                    >
                                        Annuler
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            {{-- État vide --}}
            @elseif(empty($conversations) && !$conversationId)
                <div class="flex items-center justify-center min-h-full px-4 py-10">
                    <div class="w-full max-w-4xl">

                        <div class="flex justify-center mb-7">
                            <div class="flex items-center justify-center w-14 h-14 rounded-2xl bg-primary-500 text-white shadow-lg shadow-primary-500/25">
                                <x-heroicon-m-sparkles class="w-7 h-7" />
                            </div>
                        </div>

                        <div class="text-center">
                            <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight text-gray-950 dark:text-white">
                                Comment puis-je vous aider ?
                            </h1>
                            <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                                Posez une question ou choisissez une suggestion pour commencer.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-w-2xl mx-auto mt-9">
                            @php
                                $suggestions = [
                                    [
                                        'label' => 'Créer un nouveau patient',
                                        'permission' => 'patients.create',
                                        'icon' => 'user-plus',
                                    ],
                                    [
                                        'label' => 'Rechercher un rendez-vous',
                                        'permission' => 'appointments.manage',
                                        'icon' => 'calendar',
                                    ],
                                    [
                                        'label' => 'Générer une ordonnance',
                                        'permission' => 'consultations.manage',
                                        'icon' => 'document',
                                    ],
                                    [
                                        'label' => 'Consulter la facturation',
                                        'permission' => 'billing.manage',
                                        'icon' => 'banknotes',
                                    ],
                                ];
                            @endphp

                            @foreach($suggestions as $suggestion)
                                @can($suggestion['permission'])
                                    <button
                                        wire:click="$set('message', '{{ $suggestion['label'] }}')"
                                        class="group flex items-center gap-3 p-4 text-left rounded-2xl border border-gray-200 bg-white hover:border-gray-300 hover:shadow-sm dark:border-white/[0.08] dark:bg-gray-900 dark:hover:border-white/[0.15] dark:hover:bg-white/[0.03] transition-all cursor-pointer"
                                    >
                                        <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-gray-100 dark:bg-white/5 shrink-0">
                                            @if($suggestion['icon'] === 'user-plus')
                                                <x-heroicon-m-user-plus class="w-5 h-5 text-gray-500 dark:text-gray-400" />
                                            @elseif($suggestion['icon'] === 'calendar')
                                                <x-heroicon-m-calendar-days class="w-5 h-5 text-gray-500 dark:text-gray-400" />
                                            @elseif($suggestion['icon'] === 'document')
                                                <x-heroicon-m-document-text class="w-5 h-5 text-gray-500 dark:text-gray-400" />
                                            @else
                                                <x-heroicon-m-banknotes class="w-5 h-5 text-gray-500 dark:text-gray-400" />
                                            @endif
                                        </div>

                                        <span class="flex-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                                            {{ $suggestion['label'] }}
                                        </span>

                                        <x-heroicon-m-arrow-up-right class="w-4 h-4 text-gray-300 dark:text-gray-600 opacity-0 group-hover:opacity-100 transition-opacity" />
                                    </button>
                                @endcan
                            @endforeach
                        </div>

                    </div>
                </div>

            {{-- Messages --}}
            @else
                @php
                    $messages = $this->getConversationMessages();
                @endphp

                <div class="w-full max-w-4xl mx-auto px-4 md:px-6 py-8">
                    <div class="space-y-7">

                        @foreach($messages as $msg)

                            @if($msg->role === 'user')
                                <div class="flex justify-end">
                                    <div class="flex items-end gap-2 max-w-[85%] md:max-w-[75%]">
                                        <div class="px-4 py-3 rounded-2xl rounded-br-md bg-gray-100 dark:bg-white/5 text-[15px] leading-6 text-gray-900 dark:text-gray-100 whitespace-pre-wrap">
                                            {{ $msg->content }}
                                        </div>
                                    </div>
                                </div>

                            @elseif($msg->role === 'assistant' && $msg->content)
                                <div class="flex items-start gap-3">
                                    <div class="flex items-center justify-center w-8 h-8 rounded-full bg-primary-500 text-white shrink-0">
                                        <x-heroicon-m-sparkles class="w-4 h-4" />
                                    </div>
                                    <div class="flex-1 min-w-0 pt-0.5 text-[15px] leading-7 text-gray-800 dark:text-gray-200 whitespace-pre-wrap">
                                        {!! nl2br(e($msg->content)) !!}
                                    </div>
                                </div>
                            @endif

                        @endforeach

                        {{-- Indicateur de chargement --}}
                        @if($isLoading)
                            <div class="flex items-start gap-3">
                                <div class="flex items-center justify-center w-8 h-8 rounded-full bg-primary-500 text-white shrink-0">
                                    <x-heroicon-m-sparkles class="w-4 h-4" />
                                </div>
                                <div class="flex items-center gap-1.5 h-8">
                                    <span class="w-2 h-2 rounded-full bg-gray-400 dark:bg-white/40 animate-bounce" style="animation-delay:0ms"></span>
                                    <span class="w-2 h-2 rounded-full bg-gray-400 dark:bg-white/40 animate-bounce" style="animation-delay:150ms"></span>
                                    <span class="w-2 h-2 rounded-full bg-gray-400 dark:bg-white/40 animate-bounce" style="animation-delay:300ms"></span>
                                </div>
                            </div>
                        @endif

                    </div>
                </div>
            @endif

        </div>

        {{-- Zone de saisie --}}
        @if(!$pendingConfirmation)
            <div class="shrink-0 bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-white/10 px-4 md:px-6 pt-3 pb-4">
                <div class="w-full max-w-4xl mx-auto">

                    <div class="relative flex items-end w-full rounded-2xl border border-gray-300 bg-white shadow-sm dark:border-white/[0.12] dark:bg-white/5 focus-within:border-primary-500 dark:focus-within:border-primary-500/50 transition-all">

                        <button
                            type="button"
                            class="flex items-center justify-center w-9 h-9 ml-1.5 mb-1.5 rounded-xl text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/[0.06] transition-colors cursor-pointer"
                            title="Ajouter"
                        >
                            <x-heroicon-m-plus class="w-[18px] h-[18px]" />
                        </button>

                        <textarea
                            wire:model.live="message"
                            rows="1"
                            placeholder="Envoyez un message..."
                            x-ref="chatInput"
                            @keydown.enter.prevent="if(!$event.shiftKey && !$wire.isLoading) $wire.sendMessage()"
                            x-on:input="$el.style.height = 'auto'; $el.style.height = Math.min($el.scrollHeight, 200) + 'px'"
                            class="flex-1 resize-none bg-transparent px-2.5 py-3.5 text-[15px] text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-white/30 focus:outline-none leading-6"
                            style="min-height:48px;max-height:200px;"
                        ></textarea>

                        <div class="flex items-center pr-2 pb-2">
                            <button
                                wire:click="sendMessage"
                                wire:loading.attr="disabled"
                                :disabled="$wire.message.trim() === ''"
                                class="flex items-center justify-center w-8 h-8 rounded-full transition-all cursor-pointer
                                enabled:bg-primary-600 enabled:text-white enabled:hover:bg-primary-500
                                disabled:bg-gray-200 disabled:text-gray-400 disabled:cursor-not-allowed
                                dark:enabled:bg-primary-500 dark:enabled:text-white dark:enabled:hover:bg-primary-400
                                dark:disabled:bg-white/[0.08] dark:disabled:text-white/20"
                                title="Envoyer"
                            >
                                <x-heroicon-m-arrow-up class="w-4 h-4" />
                            </button>
                        </div>

                    </div>

                    <p class="text-[11px] text-center text-gray-400 dark:text-white/20 mt-2.5 px-2">
                        L'assistant IA peut faire des erreurs. Vérifiez les informations importantes.
                    </p>
                </div>
            </div>
        @endif

    </main>

    {{-- Script Alpine.js --}}
    <script>
        function aiChat() {
            return {
                sidebarOpen: localStorage.getItem('ai-chat-sidebar') !== 'false',
                isMobile: window.innerWidth < 1024,

                init() {
                    window.addEventListener('resize', () => {
                        this.isMobile = window.innerWidth < 1024;
                    });

                    this.$watch('sidebarOpen', (value) => {
                        localStorage.setItem('ai-chat-sidebar', value);
                    });
                },

                scrollToBottom() {
                    const container = this.$refs.messagesContainer;
                    if (container) {
                        container.scrollTop = container.scrollHeight;
                    }
                    const input = this.$refs.chatInput;
                    if (input) {
                        input.focus();
                    }
                }
            };
        }
    </script>

</div>
