<?php

namespace Kirschbaum\Commentions;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use Kirschbaum\Commentions\Actions\SaveComment;
use Kirschbaum\Commentions\Contracts\Commenter;

trait HasComments
{
    public function comments(): MorphMany
    {
        return $this->morphMany(Config::getCommentModel(), 'commentable');
    }

    public function commentsQuery(): MorphMany
    {
        return $this->comments()
            ->latest()
            ->with(['author', 'reactions.reactor']);
    }

    public function comment(string $body, ?Commenter $author): Comment
    {
        return SaveComment::run($this, $author, $body);
    }

    public function getComments(): Collection
    {
        return $this->commentsQuery()->get();
    }

    /**
     * Subscribe a user to this commentable.
     */
    public function subscribe(Commenter $subscriber): void
    {
        CommentSubscription::query()->firstOrCreate([
            'subscribable_type' => $this->getMorphClass(),
            'subscribable_id' => $this->getKey(),
            'subscriber_type' => $subscriber->getMorphClass(),
            'subscriber_id' => $subscriber->getKey(),
        ]);
    }

    /**
     * Unsubscribe a user from this commentable.
     */
    public function unsubscribe(Commenter $subscriber): void
    {
        CommentSubscription::query()->where([
            'subscribable_type' => $this->getMorphClass(),
            'subscribable_id' => $this->getKey(),
            'subscriber_type' => $subscriber->getMorphClass(),
            'subscriber_id' => $subscriber->getKey(),
        ])->delete();
    }

    /**
     * Get all subscribers for this commentable.
     *
     * @return Collection<int, Commenter>
     */
    public function getSubscribers(): Collection
    {
        $commenterModel = Config::getCommenterModel();

        return CommentSubscription::query()
            ->where('subscribable_type', $this->getMorphClass())
            ->where('subscribable_id', $this->getKey())
            ->get()
            ->map(function (CommentSubscription $subscription) use ($commenterModel) {
                return $commenterModel::whereKey($subscription->subscriber_id)->first();
            })
            ->filter();
    }
}
