<?php

namespace Kirschbaum\Commentions\Actions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Kirschbaum\Commentions\Comment;
use Kirschbaum\Commentions\Config;
use Kirschbaum\Commentions\Contracts\Commenter;
use Kirschbaum\Commentions\Events\CommentWasCreatedEvent;
use Kirschbaum\Commentions\Events\UserIsSubscribedToCommentableEvent;
use Kirschbaum\Commentions\Events\UserWasMentionedEvent;

class SaveComment
{
    /**
     * @throws AuthorizationException
     */
    public function __invoke(Model $commentable, Commenter $author, string $body, ?Collection $attachments = null): Comment
    {
        if ($author->cannot('create', Config::getCommentModel())) {
            throw new AuthorizationException('Cannot create comment');
        }

        $comment = $commentable->comments()->create([
            'body' => $body,
            'author_id' => $author->getKey(),
            'author_type' => $author->getMorphClass(),
        ]);

        // Handle file attachments
        if ($attachments && $attachments->isNotEmpty()) {
            $this->handleAttachments($comment, $attachments);
        }

        $this->dispatchEvents($comment);

        return $comment;
    }

    protected function handleAttachments(Comment $comment, Collection $attachments): void
    {
        $attachments->each(function ($attachment) use ($comment) {
            if (is_string($attachment)) {
                // Filament FileUpload stores files as strings (paths)
                CommentAttachment::create([
                    'comment_id' => $comment->id,
                    'filename' => basename($attachment),
                    'original_name' => basename($attachment),
                    'file_path' => $attachment,
                    'file_size' => 0, // Will be updated if needed
                    'mime_type' => 'application/octet-stream',
                    'disk' => config('commentions.uploads.disk', 'local'),
                    'metadata' => [
                        'uploaded_at' => now()->toISOString(),
                    ],
                ]);
            }
        });
    }

    protected function dispatchEvents(Comment $comment): void
    {
        if ($comment->wasRecentlyCreated) {
            CommentWasCreatedEvent::dispatch($comment);
        }

        $mentionees = $comment->getMentioned();

        $mentionees->each(function ($mentionee) use ($comment) {
            UserWasMentionedEvent::dispatch($comment, $mentionee);
        });

        if (config('commentions.subscriptions.auto_subscribe_on_mention', true)
            && method_exists($comment->commentable, 'subscribe')
        ) {
            $mentionees->each(function (Commenter $mentionee) use ($comment) {
                $comment->commentable->subscribe($mentionee);
            });
        }

        $subscribers = method_exists($comment->commentable, 'getSubscribers')
            ? $comment->commentable->getSubscribers()
            : collect();

        if ($subscribers->isNotEmpty()) {
            $excludeIds = collect([$comment->author_id])
                ->merge($mentionees->map(fn ($u) => $u->getKey()))
                ->unique()
                ->all();

            $subscribers
                ->filter(fn ($subscriber) => ! in_array($subscriber->getKey(), $excludeIds, true))
                ->each(function (Commenter $subscriber) use ($comment) {
                    if (config('commentions.subscriptions.dispatch_as_mention', false)) {
                        UserWasMentionedEvent::dispatch($comment, $subscriber);
                    } else {
                        UserIsSubscribedToCommentableEvent::dispatch($comment, $subscriber);
                    }
                });
        }

        if (config('commentions.subscriptions.auto_subscribe_on_comment', true)
            && method_exists($comment->commentable, 'subscribe')
        ) {
            // Only subscribe if not already subscribed
            if (method_exists($comment->commentable, 'isSubscribed')) {
                if (! $comment->commentable->isSubscribed($comment->author)) {
                    $comment->commentable->subscribe($comment->author);
                }
            } else {
                $comment->commentable->subscribe($comment->author);
            }
        }
    }

    public static function run(...$args)
    {
        return (new static())(...$args);
    }
}
