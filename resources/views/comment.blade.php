<div class="flex items-start space-x-4 border p-4 rounded-lg shadow-sm mb-2">
    <img
        src="{{ filament()->getUserAvatarUrl($comment->author) }}"
        alt="User Avatar"
        class="w-10 h-10 rounded-full mt-1"
    />

    <div class="flex-1">
        <div class="text-sm font-bold text-gray-900 flex justify-between items-center">
            <div>
                {{ $comment->author->name }}
                <span
                    class="text-xs text-gray-500"
                    title="Commented at {{ $comment->created_at->format('Y-m-d H:i:s') }}"
                >{{ $comment->created_at->diffForHumans() }}</span>

                @if ($comment->updated_at->gt($comment->created_at))
                    <span
                        class="text-xs text-gray-300 ml-1"
                        title="Edited at {{ $comment->updated_at->format('Y-m-d H:i:s') }}"
                    >(edited)</span>
                @endif
            </div>

            @if ($comment->isAuthor(auth()->user()))
                <x-filament::icon-button
                    icon="heroicon-s-pencil-square"
                    wire:click="edit"
                    size="xs"
                    color="gray"
                >
                </x-filament::icon-button>
            @endif
        </div>

        @if ($editing)
            <div class="mt-2">
                <div class="tip-tap-container mb-2" wire:ignore>
                    <div x-data="editor(@js($commentBody), @js($mentionables), 'comment')">
                        <div x-ref="element"></div>
                    </div>
                </div>

                <div class="flex space-x-2">
                    <x-filament::button
                        wire:click="updateComment({{ $comment->id }})"
                        size="sm"
                    >
                        Save
                    </x-filament::button>

                    <x-filament::button
                        wire:click="cancelEditing"
                        size="sm"
                        color="gray"
                    >
                        Cancel
                    </x-filament::button>
                </div>
            </div>
        @else
            <div class="mt-1 space-y-6 text-sm text-gray-800">{!! $comment->body_parsed !!}</div>
        @endif
    </div>
</div>
