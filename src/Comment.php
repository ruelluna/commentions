<?php

namespace Kirschbaum\FilamentComments;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Comment extends Model
{
    protected $fillable = [
        'body',
    ];

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }
}
