<?php

namespace Kirschbaum\Commentions\Actions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Kirschbaum\Commentions\Comment;
use Kirschbaum\Commentions\CommentAttachment;
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
    public function __invoke(Model $commentable, Commenter $author, string $body, array $attachments = []): Comment
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
        if (! empty($attachments)) {
            $this->handleAttachments($comment, $attachments);
        }

        $this->dispatchEvents($comment);

        // Reload the comment with attachments to ensure they're available
        return $comment->load('attachments');
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

    protected function handleAttachments(Comment $comment, array $attachments): void
    {
        $disk = config('commentions.uploads.disk', 'local');
        $basePath = config('commentions.uploads.path', 'commentions/attachments');
        $visibility = config('commentions.uploads.visibility', 'public');

        foreach ($attachments as $file) {
            // Generate unique filename
            $filename = uniqid() . '_' . $file['name'];
            $filePath = $basePath . '/' . $filename;

            // Decode base64 content and store the file with specified visibility
            $decodedContent = base64_decode($file['content']);
            Storage::disk($disk)->put($filePath, $decodedContent, $visibility);

            // Create attachment record
            CommentAttachment::create([
                'comment_id' => $comment->id,
                'filename' => $filename,
                'original_name' => $file['name'],
                'file_path' => $filePath,
                'file_size' => $file['size'],
                'mime_type' => $file['type'],
                'disk' => $disk,
                'metadata' => [
                    'uploaded_at' => now()->toISOString(),
                ],
            ]);
        }
    }

    public static function run(...$args)
    {
        return (new static())(...$args);
    }
}
