<?php

namespace Kirschbaum\FilamentComments\Livewire;

use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Kirschbaum\FilamentComments\Actions\SaveComment;
use Kirschbaum\FilamentComments\Contracts\CommentAuthor;

class Comments extends Component
{
    public Model $record;

    /**
     * @var CommentAuthor[]
     */
    public array $mentionables = [];

    public string $commentBody = '';

    public $editingCommentId = null;
    public $editingCommentBody = '';

    protected $rules = [
        'commentBody' => 'required|string',
    ];

    public function save()
    {
        $this->validate();

        /** @var CommentAuthor */
        $author = auth()->user();

        SaveComment::run($this->record, $author, $this->commentBody);

        $this->clear();
        unset($this->comments);
    }

    public function render()
    {
        return view('filament-comments::comments');
    }

    #[On('editorContentUpdated')]
    public function updateCommentBodyContent($value): void
    {
        if ($this->editingCommentId) {
            $this->editingCommentBody = $value;
            return;
        }

        $this->commentBody = $value;
    }

    public function clear(): void
    {
        $this->commentBody = '';

        $this->dispatch('editorContentCleared');
    }

    #[Computed]
    public function comments(): Collection
    {
        return $this->record->comments()->latest()->with('author')->get();
    }

    public function startEditing($commentId): void
    {
        $comment = $this->record->comments()->find($commentId);

        if ($comment->author_id !== auth()->id()) {
            return;
        }

        $this->editingCommentId = $commentId;
        $this->editingCommentBody = $comment->body;
    }

    public function updateComment($commentId)
    {
        $comment = $this->record->comments()->find($commentId);

        if ($comment->author_id !== auth()->id()) {
            dump('nope??');
            return;
        }

        $comment->update([
            'body' => $this->editingCommentBody,
        ]);

        $this->editingCommentId = null;
        $this->editingCommentBody = '';
    }

    public function cancelEditing()
    {
        $this->editingCommentId = null;
        $this->editingCommentBody = '';
    }
}
