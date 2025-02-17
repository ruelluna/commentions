<?php

namespace Kirschbaum\Commentions\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use Illuminate\Support\Collection;
use Kirschbaum\Commentions\Config;
use Livewire\Attributes\Renderless;
use Illuminate\Database\Eloquent\Model;
use Kirschbaum\Commentions\Actions\SaveComment;
use Kirschbaum\Commentions\Contracts\Commenter;

class Comments extends Component
{
    public Model $record;

    /**
     * @var Commenter[]
     */
    public array|Collection $mentionables = [];

    public string $commentBody = '';

    protected $rules = [
        'commentBody' => 'required|string',
    ];

    public function save()
    {
        $this->validate();

        SaveComment::run(
            $this->record,
            Config::resolveAuthenticatedUser(),
            $this->commentBody
        );

        $this->clear();
        $this->dispatch('comment:saved');
    }

    public function render()
    {
        return view('commentions::comments');
    }

    #[On('body:updated')]
    #[Renderless]
    public function updateCommentBodyContent($value): void
    {
        $this->commentBody = $value;
    }

    #[Renderless]
    public function clear(): void
    {
        $this->commentBody = '';

        $this->dispatch('comments:content:cleared');
    }

    #[Computed]
    public function mentions()
    {
        return is_callable($this->mentionables)
            ? call_user_func_array($this->mentionables, [$this->record])
            : $this->mentionables;
    }
}
