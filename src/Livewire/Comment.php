<?php

namespace Kirschbaum\Commentions\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use Kirschbaum\Commentions\Config;
use Filament\Notifications\Notification;
use Kirschbaum\Commentions\Contracts\Commenter;
use Kirschbaum\Commentions\Comment as CommentModel;

class Comment extends Component
{
    public CommentModel $comment;

    /**
     * @var Commenter[]
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
        if (! $this->comment->isAuthor(Config::resolveAuthenticatedUser())) {
            return;
        }

        $this->comment->delete();
        $this->showDeleteModal = false;

        $this->dispatch('comment:deleted');

        Notification::make()
            ->title('Comment deleted')
            ->success()
            ->send();
    }

    public function render()
    {
        return view('commentions::comment');
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
