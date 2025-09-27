<?php

namespace Kirschbaum\Commentions;

use Closure;
use InvalidArgumentException;
use Kirschbaum\Commentions\Contracts\Commenter;

class Config
{
    protected static ?string $guard = null;

    protected static ?Closure $resolveAuthenticatedUser = null;

    protected static ?Closure $resolveCommentUrl = null;

    public static function resolveAuthenticatedUserUsing(Closure $callback): void
    {
        static::$resolveAuthenticatedUser = $callback;
    }

    public static function resolveAuthenticatedUser(): ?Commenter
    {
        $resolver = static::$resolveAuthenticatedUser;
        $user = $resolver ? call_user_func($resolver) : auth()->guard(static::$guard)->user();

        if ($user !== null && ! ($user instanceof Commenter)) {
            throw new InvalidArgumentException('Resolved user must implement ' . Commenter::class);
        }

        return $user;
    }

    public static function getCommentTable(): string
    {
        return config('commentions.tables.comments', 'comments');
    }

    public static function getCommentReactionTable(): string
    {
        return config('commentions.tables.comment_reactions', 'comment_reactions');
    }

    public static function resolveCommentUrlUsing(Closure $callback): void
    {
        static::$resolveCommentUrl = $callback;
    }

    public static function resolveCommentUrl(?Comment $comment): ?string
    {
        if ($comment === null) {
            return null;
        }

        if (static::$resolveCommentUrl instanceof Closure) {
            return call_user_func(static::$resolveCommentUrl, $comment);
        }

        return null;
    }

    public static function getCommentModel(): string
    {
        return config('commentions.comment.model', Comment::class);
    }

    public static function getCommenterModel(): string
    {
        return config('commentions.commenter.model');
    }

    public static function getAttachmentModel(): string
    {
        return config('commentions.attachment.model', CommentAttachment::class);
    }

    public static function getAllowedReactions(): array
    {
        return config('commentions.reactions.allowed', ['👍']);
    }
}
