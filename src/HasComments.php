<?php

namespace Kirschbaum\Commentions;

use Kirschbaum\Commentions\Actions\SaveComment;
use Kirschbaum\Commentions\Contracts\Commenter;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasComments
{
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
    public function comment(string $body, ?Commenter $author): Comment

    {
        return SaveComment::run($this, $author, $body);
    }
}
