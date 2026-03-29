<x-filament-panels::page>
    <form wire:submit="submit">
        {{ $this->form }}

        <div class="mt-4">
            <x-filament::button type="submit" wire:loading.attr="disabled">
                <span wire:loading.remove>Ask</span>
                <span wire:loading>Processing...</span>
            </x-filament::button>
        </div>
    </form>

    @if ($answer)
        <div class="mt-6 space-y-4">
            <div class="rounded-lg bg-white dark:bg-gray-800 p-4 shadow">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Answer</h3>
                <div class="mt-2 prose dark:prose-invert max-w-none">
                    {!! nl2br(e($answer)) !!}
                </div>
            </div>

            @if ($timing)
                <div class="rounded-lg bg-white dark:bg-gray-800 p-4 shadow">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Timing</h3>
                    <div class="mt-2 grid grid-cols-3 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500">Retrieval:</span>
                            <span class="font-mono">{{ $timing['retrieval_ms'] }}ms</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Generation:</span>
                            <span class="font-mono">{{ $timing['generation_ms'] }}ms</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Total:</span>
                            <span class="font-mono">{{ $timing['total_ms'] }}ms</span>
                        </div>
                    </div>
                </div>
            @endif

            @if (count($sources) > 0)
                <div class="rounded-lg bg-white dark:bg-gray-800 p-4 shadow">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Sources</h3>
                    <div class="mt-2 space-y-2">
                        @foreach ($sources as $index => $source)
                            <div class="rounded border border-gray-200 dark:border-gray-700 p-3 text-sm">
                                <div class="flex justify-between">
                                    <span class="font-medium">#{{ $index + 1 }} — {{ $source['source'] }}</span>
                                    <span class="text-gray-500 font-mono">{{ number_format($source['score'], 4) }}</span>
                                </div>
                                <p class="mt-1 text-gray-600 dark:text-gray-400">{{ Str::limit($source['preview'], 200) }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif
</x-filament-panels::page>
