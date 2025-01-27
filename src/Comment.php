<?php

namespace Kirschbaum\FilamentComments;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Kirschbaum\FilamentComments\Actions\ParseComment;
use Kirschbaum\FilamentComments\Actions\HtmlToMarkdown;
use Kirschbaum\FilamentComments\Contracts\CommentAuthor;

class Comment extends Model
{
    protected $fillable = [
        'body',
        'author_type',
        'author_id',
    ];

    public function author(): MorphTo
    {
        return $this->morphTo();
    }

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function bodyParsed(): Attribute
    {
        return Attribute::make(
            get: fn () => ParseComment::run($this->body),
        );
    }

    public function bodyMarkdown(): Attribute
    {
        return Attribute::make(
            get: fn () => HtmlToMarkdown::run($this->body),
        );
    }

    public function isAuthor(CommentAuthor $author)
    {
        return $this->author_id === $author->getKey();
    }
}
