<div class="space-y-2" x-data="{ wasFocused: false }">
    <form wire:submit.prevent="save" x-cloak>
        {{-- tiptap editor --}}
        <div class="relative tip-tap-container mb-2" id="tip-tap-container" x-on:click="wasFocused = true">
            <div x-data="editor(@js($commentBody), @js($mentionables), 'comments')" wire:ignore>
                <div x-ref="element"></div>
            </div>
        </div>

        <template x-if="wasFocused">
            <div>
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
        </template>
    </form>

    <livewire:commentions::comment-list :record="$record" />
</div>
