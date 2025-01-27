<?php

namespace Kirschbaum\FilamentComments\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphMany;

interface Commentable
{
    public function comments(): MorphMany;

    public function commentableResourceUrl(): string;
}
