<?php

namespace Kirschbaum\Commentions\Actions;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Kirschbaum\Commentions\Comment;
use Kirschbaum\Commentions\Contracts\Commenter;
use Kirschbaum\Commentions\Events\UserWasMentionedEvent;

class SaveComment
{
    public static function run(...$args)
    {
        return (new static)(...$args);
    }

    public function __invoke(Model $commentable, Commenter $author, string $body): Comment
    {
        $comment = $commentable->comments()->create([
            'body' => $body,
            'author_id' => $author->getKey(),
            'author_type' => $author->getMorphClass(),
        ]);

        $this->dispatchMentionEvents($comment, $body);

        return $comment;
    }

    protected function dispatchMentionEvents($comment, $body): void
    {
        $ids = $this->getMentionIds($body);

        if (count($ids) === 0) {
            return;
        }

        $userModel = config('commentions.user_model');
        $users = $userModel::find($this->getMentionIds($body));

        $users->each(function ($user) use ($comment) {
            UserWasMentionedEvent::dispatch($comment, $user);
        });
    }

    /**
     * @return array<int>
     */
    protected function getMentionIds($body): array
    {
        // find any span with data-type="mention" and return the data-mention-id
        preg_match_all('/<span[^>]*data-type="mention"[^>]*data-id="(\d+)"[^>]*>/', $body, $matches);

        return $matches[1] ?? [];
    }
}
