<?php

namespace Kirschbaum\FilamentComments\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use Illuminate\Database\Eloquent\Model;

class Comments extends Component
{
    public Model $record;

    public string $commentBody = '';

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

        $this->record->comments()->create([
            'body' => $this->commentBody,
        ]);

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
}
