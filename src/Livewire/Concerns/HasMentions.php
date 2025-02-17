<?php

namespace Kirschbaum\Commentions\Livewire\Concerns;

use Livewire\Attributes\Computed;
use Illuminate\Support\Collection;
use Filament\Models\Contracts\HasName;
use Kirschbaum\Commentions\Contracts\Commenter;

trait HasMentions
{
    /**
     * @var Commenter[]
     */
    public array|Collection $mentionables = [];

    #[Computed]
    public function mentions()
    {
        return collect($this->mentionables)
            ->map(function ($mentionable) {
                return is_array($mentionable) ?
                    [
                        'id' => data_get($mentionable, 'id'),
                        'name' => data_get($mentionable, 'name'),
                    ] :
                    [
                    'id' => $mentionable->getKey(),
                    'name' => method_exists($mentionable, 'getCommenterName')
                        ? call_user_func_array([$mentionable, 'getCommenterName'], [])
                        : (
                            $mentionable instanceof HasName
                                ? $mentionable->getFilamentName()
                                : $mentionable->name
                        ),
                ];
            });
    }
}
