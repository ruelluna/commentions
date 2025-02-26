<?php

namespace Kirschbaum\Commentions\Livewire;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Kirschbaum\Commentions\Livewire\Concerns\HasMentions;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class CommentList extends Component
{
    use HasMentions;

    public Model $record;

    public bool $pollingEnabled = false;

    public int $pollingInterval = 60;

    public function render()
    {
        return view('commentions::comment-list');
    }

    #[Computed]
    public function comments(): Collection
    {
        return $this->record->getComments();
    }

    #[On('comment:saved')]
    #[On('comment:updated')]
    #[On('comment:deleted')]
    public function reloadComments(): void
    {
        unset($this->comments);
    }
}
