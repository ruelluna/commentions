<?php

namespace Kirschbaum\FilamentComments\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use Kirschbaum\FilamentComments\Comment as CommentModel;
use Kirschbaum\FilamentComments\Contracts\CommentAuthor;

class Comment extends Component
{
    public CommentModel $comment;

    /**
     * @var CommentAuthor[]
     */
    public array $mentionables = [];

    public string $commentBody = '';
    public bool $editing = false;
    public bool $showDeleteModal = false;

    protected $rules = [
        'commentBody' => 'required|string',
    ];


    public function delete()
    {
        if (! $this->comment->isAuthor(auth()->user())) {
            return;
        }

        $this->comment->delete();
        $this->showDeleteModal = false;

        $this->dispatch('comment:deleted');
    }

    public function render()
    {
        return view('filament-comments::comment');
    }

    #[On('body:updated')]
    public function updateCommentBodyContent($value): void
    {
        $this->commentBody = $value;
    }

    public function clear(): void
    {
        $this->commentBody = '';

        $this->dispatch('comment:content:cleared');
    }

    public function edit(): void
    {
        if (! $this->comment->isAuthor(auth()->user())) {
            return;
        }

        $this->editing = true;
        $this->commentBody = $this->comment->body;

        $this->dispatch('comment:updated');
    }

    public function updateComment()
    {
        if (! $this->comment->isAuthor(auth()->user())) {
            dump('nope??');
            return;
        }

        $this->comment->update([
            'body' => $this->commentBody,
        ]);

        $this->editing = false;
    }

    public function cancelEditing()
    {
        $this->editing = false;
        $this->commentBody = '';
    }
}
