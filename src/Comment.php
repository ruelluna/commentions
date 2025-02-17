<?php

namespace Kirschbaum\Commentions;

use App\Models\User;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Kirschbaum\Commentions\Contracts\Commenter;
use Kirschbaum\Commentions\Actions\ParseComment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Collection;
use Kirschbaum\Commentions\Contracts\Commentable;
use Kirschbaum\Commentions\Actions\HtmlToMarkdown;
use Kirschbaum\Commentions\Database\Factories\CommentFactory;

/**
 * @property int $id
 * @property string $body
 * @property string $body_markdown
 * @property string $body_parsed
 * @property User $author
 * @property Commentable $commentable
 */
class Comment extends Model
{
    use HasFactory;

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

    public function getBodyMarkdown(Closure $mentionedCallback = null): string
    {
        return HtmlToMarkdown::run(
            html: $this->body,
            mentionedCallback: $mentionedCallback,
        );
    }

    public function isAuthor(Commenter $author)
    {
        return $this->author_id === $author->getKey();
    }

    protected static function newFactory()
    {
        return CommentFactory::new();
    }

    /**
     * Get the IDs of users mentioned in the comment body.
     *
     * @return Collection<Commenter>
     */
    public function getMentioned(): Collection
    {
        $userModel = config('commentions.user_model');

        preg_match_all(
            '/<span[^>]*data-type="mention"[^>]*data-id="(\d+)"[^>]*>/',
            $this->body,
            $matches
        );

        return collect($matches[1] ?? [])
            ->map(fn ($userId) => $userModel::find($userId))
            ->filter(fn ($mentioned) => $mentioned !== null);
    }
}
