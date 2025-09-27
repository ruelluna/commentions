<?php

namespace Kirschbaum\Commentions\Livewire;

use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Kirschbaum\Commentions\Actions\SaveComment;
use Kirschbaum\Commentions\Config;
use Kirschbaum\Commentions\Livewire\Concerns\HasMentions;
use Kirschbaum\Commentions\Livewire\Concerns\HasPagination;
use Kirschbaum\Commentions\Livewire\Concerns\HasPolling;
use Kirschbaum\Commentions\Livewire\Concerns\HasSidebar;
use Kirschbaum\Commentions\Rules\FileUploadRule;
use Livewire\Attributes\On;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Livewire\WithFileUploads;

class Comments extends Component
{
    use HasMentions;
    use HasPagination;
    use HasPolling;
    use HasSidebar;
    use WithFileUploads;

    public Model $record;

    public string $commentBody = '';

    public $attachments = [];

    protected function rules(): array
    {
        return [
            'commentBody' => 'required|string',
            'attachments' => [new FileUploadRule()],
        ];
    }

    public function getFileUploadComponent(): FileUpload
    {
        return FileUpload::make('attachments')
            ->label('Attachments')
            ->multiple()
            ->maxFiles(config('commentions.uploads.max_files', 5))
            ->maxSize(config('commentions.uploads.max_file_size', 10240))
            ->acceptedFileTypes(config('commentions.uploads.allowed_types', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt', 'zip']))
            ->disk(config('commentions.uploads.disk', 'local'))
            ->directory(config('commentions.uploads.path', 'commentions/attachments'))
            ->visibility('private')
            ->storeFileNamesIn('original_names')
            ->reorderable()
            ->appendFiles()
            ->previewable()
            ->downloadable()
            ->openable();
    }

    #[Renderless]
    public function save()
    {
        $this->validate();

        $attachments = collect($this->attachments)->filter();

        SaveComment::run(
            $this->record,
            Config::resolveAuthenticatedUser(),
            $this->commentBody,
            $attachments
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
        $this->attachments = [];

        $this->dispatch('comments:content:cleared');
        $this->dispatch('files:cleared');
    }

    public function removeAttachment($index): void
    {
        unset($this->attachments[$index]);
        $this->attachments = array_values($this->attachments);
    }
}
