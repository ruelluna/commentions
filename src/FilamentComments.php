<?php

namespace Kirschbaum\Commentions;

use Closure;
use Kirschbaum\Commentions\Contracts\Commenter;

class FilamentComments
{
    protected static ?Closure $resolveAuthenticatedUser = null;

    public static function resolveAuthenticatedUserUsing(Closure $callback): void
    {
        static::$resolveAuthenticatedUser = $callback;
    }

    public static function resolveAuthenticatedUser(): Commenter
    {
        return static::$resolveAuthenticatedUser
            ? call_user_func(static::$resolveAuthenticatedUser)
            : auth()->user();
    }
}
