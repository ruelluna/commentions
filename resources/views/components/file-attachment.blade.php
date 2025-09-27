@props(['attachment'])

<div
    class="comm:inline-flex comm:items-center comm:space-x-2 comm:bg-gray-50 comm:dark:bg-gray-800 comm:rounded-lg comm:px-3 comm:py-2 comm:mb-2">
    @if ($attachment->isImage())
        <img src="{{ $attachment->url }}" alt="{{ $attachment->original_name }}"
            class="comm:h-8 comm:w-8 comm:rounded comm:object-cover">
    @else
        <x-heroicon-o-document class="comm:h-5 comm:w-5 comm:text-gray-400" />
    @endif

    <div class="comm:flex-1 comm:min-w-0">
        <p class="comm:text-sm comm:font-medium comm:text-gray-900 comm:dark:text-gray-100 comm:truncate">
            {{ $attachment->original_name }}
        </p>
        <p class="comm:text-xs comm:text-gray-500 comm:dark:text-gray-400">
            {{ $attachment->human_readable_size }}
        </p>
    </div>

    <a href="{{ $attachment->url }}" target="_blank"
        class="comm:text-blue-600 comm:hover:text-blue-800 comm:dark:text-blue-400 comm:dark:hover:text-blue-300">
        <x-heroicon-o-arrow-down-tray class="comm:h-4 comm:w-4" />
    </a>
</div>
