<?php

namespace Kirschbaum\Commentions\Livewire;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Kirschbaum\Commentions\Livewire\Concerns\HasMentions;
use Kirschbaum\Commentions\Livewire\Concerns\HasPagination;
use Kirschbaum\Commentions\Livewire\Concerns\HasPolling;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class CommentList extends Component
{
    use HasMentions;
    use HasPagination;
    use HasPolling;

    public Model $record;

    public function render()
    {
        return view('commentions::comment-list');
    }

    #[Computed]
    public function comments(): Collection
    {
        return $this->record->getComments($this->paginate ? $this->perPage : null);
    }

    #[On('comment:saved')]
    #[On('comment:updated')]
    #[On('comment:deleted')]
    public function reloadComments(): void
    {
        // Small delay to ensure database transaction is complete
        usleep(100000); // 100ms delay
        unset($this->comments);
    }
}
