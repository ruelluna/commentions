<?php

namespace Kirschbaum\Commentions\Filament\Actions;

use Illuminate\Support\Collection;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Model;

class CommentsTableAction extends Action
{
    public static function make(?string $name = 'comments'): static
    {
        return parent::make($name)
            ->icon('heroicon-o-chat-bubble-left-right')
            ->modalContent(fn (Model $record) => view('commentions::comments-modal', [
                'record' => $record,
            ]))
            ->modalWidth('xl')
            ->label('Comments')
            ->modalAutofocus(false);
    }

    public function mentionables(array|Collection $mentionables): self
    {
        return $this->modalContent(fn (Model $record) => view('commentions::comments-modal', [
            'record' => $record,
            'mentionables' => $mentionables,
        ]));
    }
}
