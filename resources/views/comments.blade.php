<div class="space-y-4">
    @foreach ($this->comments as $comment)
        <div class="flex items-start space-x-4 border p-4 rounded-lg shadow-sm">
            <img src="https://placehold.co/40x40/EEE/31343C" alt="User Avatar" class="w-10 h-10 rounded-full">
            <div>
                <div class="font-semibold text-gray-800">{{ $comment->commentable->name }}</div>
                <div class="text-gray-600">{{ $comment->body }}</div>
                <div class="text-sm text-gray-500">{{ $comment->created_at->diffForHumans() }}</div>
            </div>
        </div>
    @endforeach

    <form wire:submit.prevent="save" class="mt-6">
        {{-- <p class="text-red-500 mb-2">{{ $commentBody }}</p> --}}

        {{-- tiptap editor --}}
        <div class="relative tip-tap-container" id="tip-tap-container">
            <div x-data="editor(@js($commentBody), @js($this->mentions))" wire:ignore>
                <div x-ref="element"></div>
            </div>
        </div>

        <button type="submit" class="mt-3 px-5 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">Submit</button>
    </form>
</div>

