<?php

namespace Kirschbaum\FilamentComments\Livewire;

use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use Illuminate\Database\Eloquent\Model;
use Kirschbaum\FilamentComments\Actions\SaveComment;

class Comments extends Component
{
    public Model $record;

    public string $commentBody = '<p><span data-type="mention" data-id="Jennifer Grey"></span> Would you mind to share what you’ve been working on lately? We fear not much happened since Dirty Dancing.</p>';

    #[On('editorContentUpdated')]
    public function updateComment($value)
    {
        dump('damn!!!', $value);
        $this->commentBody = $value;
    }

    protected $rules = [
        'commentBody' => 'required|string',
    ];

    public function save()
    {
        $this->validate();

        SaveComment::run($this->record, $this->commentBody);

        $this->commentBody = ''; // Clear the textarea after submission
        unset($this->comments);
    }

    public function render()
    {
        return view('filament-comments::comments');
    }

    #[Computed]
    public function comments()
    {
        return $this->record->comments()->latest()->get();
    }

    #[Computed]
    public function mentions()
    {
        return User::all()->toArray();
    }

}
