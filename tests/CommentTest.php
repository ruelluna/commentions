<?php

namespace Tests;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Kirschbaum\Commentions\Comment;
use Kirschbaum\Commentions\CommentAttachment;
use Kirschbaum\Commentions\Events\UserIsSubscribedToCommentableEvent;
use Kirschbaum\Commentions\Events\UserWasMentionedEvent;
use Tests\Models\Post;
use Tests\Models\User;

test('it can save a comment', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();

    $comment = $post->comment('This is a test comment', $user);

    expect($comment)
        ->toBeInstanceOf(Comment::class)
        ->body->toBe('This is a test comment')
        ->author->toBeModel($user)
        ->commentable->toBeModel($post);

    expect($post->comments)->toHaveCount(1);
});

test('it cannot save a comment when the policy denies it', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();

    \Gate::policy(Comment::class, \Tests\Policies\BlockedCommentPolicy::class);

    expect(fn () => $post->comment('This is a test comment', $user))
        ->toThrow(AuthorizationException::class)
        ->and($post->comments)->toHaveCount(0);
});

test('it dispatches events for mentions', function () {
    Event::fake();

    $user = User::factory()->create();
    $anotherUser = User::factory()->create();
    $post = Post::factory()->create();

    $comment = $post->comment(
        sprintf('Hey <span data-type="mention" data-id="%s">@%s</span>', $anotherUser->id, $anotherUser->name),
        $user
    );

    expect($comment)
        ->toBeInstanceOf(Comment::class)
        ->body->toContain('Hey')
        ->author->toBeModel($user)
        ->commentable->toBeModel($post);

    expect($post->comments)->toHaveCount(1);

    Event::assertDispatched(UserWasMentionedEvent::class, function ($event) use ($anotherUser) {
        return $event->user->is($anotherUser);
    });
});

test('it dispatches events for subscribers (distinct event)', function () {
    Event::fake();

    config()->set('commentions.subscriptions.dispatch_as_mention', false);

    $author = User::factory()->create();
    $subscriber = User::factory()->create();
    $post = Post::factory()->create();

    $post->subscribe($subscriber);

    $comment = $post->comment('A new comment for subscribers only', $author);

    Event::assertDispatched(UserIsSubscribedToCommentableEvent::class, function ($event) use ($subscriber, $comment) {
        return $event->user->is($subscriber) && $event->comment->is($comment);
    });

    Event::assertNotDispatched(UserIsSubscribedToCommentableEvent::class, function ($event) use ($author) {
        return $event->user->is($author);
    });
});

test('it dispatches mention event for subscribers when configured', function () {
    Event::fake();

    config()->set('commentions.subscriptions.dispatch_as_mention', true);

    $author = User::factory()->create();
    $subscriber = User::factory()->create();
    $post = Post::factory()->create();

    $post->subscribe($subscriber);

    $comment = $post->comment('A new comment for subscribers as mentions', $author);

    Event::assertDispatched(UserWasMentionedEvent::class, function ($event) use ($subscriber, $comment) {
        return $event->user->is($subscriber) && $event->comment->is($comment);
    });
});

test('it can get mentioned user ids from comment', function () {
    $user = User::factory()->create();
    $mentionedUser1 = User::factory()->create();
    $mentionedUser2 = User::factory()->create();

    $comment = new Comment([
        'body' => sprintf(
            'Hey <span data-type="mention" data-id="%s">@%s</span> and <span data-type="mention" data-id="%s">@%s</span>',
            $mentionedUser1->id,
            $mentionedUser1->name,
            $mentionedUser2->id,
            $mentionedUser2->name
        ),
    ]);

    expect($comment->getMentioned())
        ->toHaveCount(2)
        ->toContain($mentionedUser1)
        ->toContain($mentionedUser2);
});

test('it can have attachments', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $post = Post::factory()->create();
    $comment = $post->comment('This is a test comment', $user);

    $attachment = CommentAttachment::create([
        'comment_id' => $comment->id,
        'filename' => 'test.pdf',
        'original_name' => 'test.pdf',
        'file_path' => 'commentions/attachments/2024/01/test.pdf',
        'file_size' => 1000,
        'mime_type' => 'application/pdf',
        'disk' => 'local',
    ]);

    expect($comment->attachments)->toHaveCount(1);
    expect($comment->attachments->first())->toBeInstanceOf(CommentAttachment::class);
    expect($comment->attachments->first()->original_name)->toBe('test.pdf');
});

test('it can have multiple attachments', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $post = Post::factory()->create();
    $comment = $post->comment('This is a test comment', $user);

    $attachment1 = CommentAttachment::create([
        'comment_id' => $comment->id,
        'filename' => 'test1.pdf',
        'original_name' => 'test1.pdf',
        'file_path' => 'commentions/attachments/2024/01/test1.pdf',
        'file_size' => 1000,
        'mime_type' => 'application/pdf',
        'disk' => 'local',
    ]);

    $attachment2 = CommentAttachment::create([
        'comment_id' => $comment->id,
        'filename' => 'test2.jpg',
        'original_name' => 'test2.jpg',
        'file_path' => 'commentions/attachments/2024/01/test2.jpg',
        'file_size' => 2000,
        'mime_type' => 'image/jpeg',
        'disk' => 'local',
    ]);

    expect($comment->attachments)->toHaveCount(2);
    expect($comment->attachments->pluck('original_name'))->toContain('test1.pdf', 'test2.jpg');
});

test('attachments are deleted when comment is deleted', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $post = Post::factory()->create();
    $comment = $post->comment('This is a test comment', $user);

    $attachment = CommentAttachment::create([
        'comment_id' => $comment->id,
        'filename' => 'test.pdf',
        'original_name' => 'test.pdf',
        'file_path' => 'commentions/attachments/2024/01/test.pdf',
        'file_size' => 1000,
        'mime_type' => 'application/pdf',
        'disk' => 'local',
    ]);

    expect($comment->attachments)->toHaveCount(1);

    $comment->delete();

    expect(CommentAttachment::count())->toBe(0);
});
