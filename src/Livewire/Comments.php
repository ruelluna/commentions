<?php

namespace Kirschbaum\FilamentComments\Livewire;

use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use Illuminate\Database\Eloquent\Model;
use Kirschbaum\FilamentComments\Actions\SaveComment;
use Kirschbaum\FilamentComments\Contracts\CommentAuthor;

class Comments extends Component
{
    public Model $record;

    public string $commentBody = '';

    #[On('editorContentUpdated')]
    public function updateComment($value)
    {
        $this->commentBody = $value;
    }

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

    public function clear()
    {
        $this->commentBody = '';
    }

    #[Computed]
    public function comments()
    {
        return $this->record->comments()->latest()->with('author')->get();
    }

    #[Computed]
    public function mentions()
    {
        return User::all()->toArray();
    }

}
