<?php

namespace Kirschbaum\Commentions\Contracts;

/**
 * @method bool can(string $ability, $arguments = [])
 * @method bool cannot(string $ability, $arguments = [])
 */
interface Commenter
{
    /**
     * Get the identifier key for the object. Usually the primary key.
     *
     * @return int|string|null
     */
    public function getKey();

    /**
     * @return string
     */
    public function getMorphClass();
}
