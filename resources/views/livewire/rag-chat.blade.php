<div class="flex flex-col h-full max-w-2xl mx-auto" x-data="{ autoScroll: true }">
    {{-- Messages --}}
    <div
        class="flex-1 overflow-y-auto space-y-4 p-4"
        x-ref="messages"
        x-effect="if (autoScroll) $refs.messages.scrollTop = $refs.messages.scrollHeight"
    >
        @forelse ($messages as $message)
            <div class="flex {{ $message['role'] === 'user' ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-[80%] rounded-lg px-4 py-2 {{ $message['role'] === 'user' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-900 dark:bg-gray-800 dark:text-gray-100' }}">
                    <div class="prose dark:prose-invert prose-sm">
                        {!! nl2br(e($message['content'])) !!}
                    </div>

                    @if (!empty($message['sources']))
                        <div class="mt-2 pt-2 border-t border-gray-200 dark:border-gray-700">
                            <button
                                wire:click="toggleSources"
                                class="text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400"
                            >
                                {{ $showSources ? 'Hide' : 'Show' }} sources ({{ count($message['sources']) }})
                            </button>

                            @if ($showSources)
                                <div class="mt-1 space-y-1">
                                    @foreach ($message['sources'] as $source)
                                        <div class="text-xs text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-900 rounded p-2">
                                            <span class="font-medium">{{ $source['source'] }}</span>
                                            <span class="text-gray-400">({{ number_format($source['score'], 2) }})</span>
                                            <p class="mt-0.5">{{ Str::limit($source['preview'], 100) }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="flex items-center justify-center h-full text-gray-400">
                <p>{{ $placeholder }}</p>
            </div>
        @endforelse

        @if ($isLoading)
            <div class="flex justify-start">
                <div class="bg-gray-100 dark:bg-gray-800 rounded-lg px-4 py-2">
                    <div class="flex space-x-1">
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Input --}}
    <div class="border-t border-gray-200 dark:border-gray-700 p-4">
        <form wire:submit.prevent="send" class="flex gap-2">
            <input
                type="text"
                wire:model="question"
                placeholder="{{ $placeholder }}"
                class="flex-1 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-gray-100"
                @disabled($isLoading)
            />
            <button
                type="submit"
                class="rounded-lg bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700 disabled:opacity-50 transition-colors"
                @disabled($isLoading)
            >
                Send
            </button>
        </form>

        <div class="flex justify-between mt-2 text-xs text-gray-400">
            <button wire:click="clearChat" class="hover:text-gray-600">Clear chat</button>
            <button wire:click="toggleSources" class="hover:text-gray-600">
                {{ $showSources ? 'Hide' : 'Show' }} sources
            </button>
        </div>
    </div>
</div>
