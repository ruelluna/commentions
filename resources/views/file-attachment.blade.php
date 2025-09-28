@use('\Kirschbaum\Commentions\Config')

<div
    class="comm:flex comm:items-center comm:gap-x-2 comm:p-2 comm:bg-gray-50 comm:dark:bg-gray-800 comm:rounded comm:border comm:border-gray-200 comm:dark:border-gray-700">
    <x-filament::icon :icon="$attachment->getFileIcon()" class="comm:w-5 comm:h-5 comm:text-gray-500" />

    <div class="comm:flex-1 comm:min-w-0">
        <a href="{{ $attachment->url }}" target="_blank"
            class="comm:text-sm comm:font-medium comm:text-blue-600 comm:dark:text-blue-400 comm:hover:underline comm:truncate comm:block"
            title="{{ $attachment->original_name }}">
            {{ $attachment->original_name }}
        </a>
        <p class="comm:text-xs comm:text-gray-500 comm:dark:text-gray-400">
            {{ $attachment->human_readable_size }}
        </p>
    </div>

    @if ($attachment->isImage())
        <x-filament::icon-button icon="heroicon-o-eye" size="xs" color="gray"
            x-on:click="$dispatch('open-modal', { id: 'image-preview-{{ $attachment->id }}' })" />
    @endif
</div>

@if ($attachment->isImage())
    <x-filament::modal id="image-preview-{{ $attachment->id }}" width="4xl">
        <x-slot name="heading">
            {{ $attachment->original_name }}
        </x-slot>

        <div class="comm:p-4">
            <img src="{{ $attachment->url }}" alt="{{ $attachment->original_name }}"
                class="comm:max-w-full comm:h-auto comm:rounded-lg" />
        </div>
    </x-filament::modal>
@endif
