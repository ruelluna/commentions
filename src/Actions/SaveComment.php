<?php

namespace Kirschbaum\FilamentComments\Actions;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Kirschbaum\FilamentComments\Contracts\CommentAuthor;
use Kirschbaum\FilamentComments\Events\UserWasMentionedEvent;

class SaveComment
{
    public static function run(...$args)
    {
        return (new static)(...$args);
    }

    public function __invoke(Model $commentable, CommentAuthor $author, string $body)
    {
        $comment = $commentable->comments()->create([
            'body' => $body,
            'author_id' => $author->getKey(),
            'author_type' => $author->getMorphClass(), // TODO: Use morph-type here
        ]);

        $this->dispatchMentionEvents($comment, $body);
    }

    protected function dispatchMentionEvents($comment, $body)
    {
        $users = User::find($this->getMentionIds($body));

        logger()->debug('Dispatching UserWasMentionedEvent events', [
            'total_mentions' => $users->count(),
        ]);

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
