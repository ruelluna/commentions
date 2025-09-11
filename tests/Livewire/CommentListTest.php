<?php

use Kirschbaum\Commentions\Livewire\CommentList;
use Mockery;
use Tests\Models\Post;

use function Pest\Livewire\livewire;

test('CommentList calls getComments when not paginating', function () {
    /** @var Post|Mockery\MockInterface $post */
    $post = Mockery::mock(Post::class)->makePartial();

    $post->shouldReceive('getComments')
        ->once()
        ->andReturn(collect());

    $component = livewire(CommentList::class, [
        'record' => $post,
        'paginate' => false,
    ]);

    $component->get('comments');
});

test('CommentList calls getComments when paginating', function () {
    /** @var Post|Mockery\MockInterface $post */
    $post = Mockery::mock(Post::class)->makePartial();

    $post->shouldReceive('getComments')
        ->once()
        ->andReturn(collect());

    $component = livewire(CommentList::class, [
        'record' => $post,
        'paginate' => true,
        'perPage' => 5,
    ]);

    $component->get('comments');
});
