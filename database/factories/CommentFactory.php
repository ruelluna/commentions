<?php

namespace Kirschbaum\FilamentComments\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Kirschbaum\FilamentComments\Comment;
use Kirschbaum\FilamentComments\Contracts\Commentable;
use Kirschbaum\FilamentComments\Contracts\CommentAuthor;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    protected $model = Comment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'body' => $this->faker->paragraph,
        ];
    }

    public function commentable(Commentable $commentable): self
    {
        return $this->state(fn (array $attributes) => [
            'commentable_type' => $commentable->getMorphClass(),
            'commentable_id' => $commentable->getKey(),
        ]);
    }

    public function author(CommentAuthor $author): self
    {
        return $this->state(fn (array $attributes) => [
            'author_type' => $author->getMorphClass(),
            'author_id' => $author->getKey(),
        ]);
    }
}
