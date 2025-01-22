<div class="space-y-4">
    <form wire:submit.prevent="save" x-data="{ wasFocused: false }">
        {{-- tiptap editor --}}
        <div class="relative tip-tap-container mb-2" id="tip-tap-container" x-on:click="wasFocused = true">
            <div x-data="editor(@js($commentBody), @js($this->mentions))" wire:ignore>
                <div x-ref="element"></div>
            </div>
        </div>

        <div x-show="wasFocused">
            <x-filament::button
                wire:click="save"
                size="sm"
            >Save</x-filament::button>

            <x-filament::button
                x-on:click="wasFocused = false"
                wire:click="clear"
                size="sm"
                 color="gray"
            >Cancel</x-filament::button>
        </div>
    </form>

    @foreach ($this->comments as $comment)
        <div class="flex items-start space-x-4 border p-4 rounded-lg shadow-sm">
            <img
                src="https://placehold.co/50x50/EEE/31343C"
                alt="User Avatar"
                class="w-10 h-10 rounded-full mt-1"
            />

            <div>
                <div class="text-sm font-bold text-gray-900">
                    {{ $comment->author->name }}
                    <span class="text-xs text-gray-500" title="Commented at {{ $comment->created_at->format('Y-m-d H:i:s') }}">{{ $comment->created_at->diffForHumans() }}</span>
                </div>

                <div class="mt-1 space-y-6 text-sm text-gray-800">{!! $comment->body_parsed !!}</div>
            </div>
        </div>
    @endforeach
</div>

