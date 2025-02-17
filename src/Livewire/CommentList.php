<?php

namespace Kirschbaum\Commentions\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Kirschbaum\Commentions\Contracts\Commenter;

class CommentList extends Component
{
    public Model $record;

    /**
     * @var Commenter[]
     */
    public array|Collection $mentionables = [];

    public function render()
    {
        return view('commentions::comment-list');
    }

    #[Computed]
    public function comments(): Collection
    {
        return $this->record->comments()->latest()->with('author')->get();
    }

    #[On('comment:saved')]
    #[On('comment:updated')]
    #[On('comment:deleted')]
    public function reloadComments(): void
    {
        unset($this->comments);
    }
}
