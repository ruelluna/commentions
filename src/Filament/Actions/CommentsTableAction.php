<?php

namespace Kirschbaum\Commentions\Filament\Actions;

use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class CommentsTableAction extends Action
{
    public function mentionables(array|Collection $mentionables): self
    {
        return $this->modalContent(fn (Model $record) => view('commentions::comments-modal', [
            'record' => $record,
            'mentionables' => $mentionables,
        ]));
    }

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
}
