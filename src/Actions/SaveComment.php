<?php

namespace Kirschbaum\FilamentComments\Actions;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Kirschbaum\FilamentComments\Events\UserWasMentionedEvent;

class SaveComment
{
    public static function run(Model $commentable, string $body)
    {
        return (new static)($commentable, $body);
    }

    public function __invoke(Model $commentable, string $body)
    {
        $comment = $commentable->comments()->create([
            'body' => $body,
        ]);

        $this->dispatchMentionEvents($comment, $body);
    }

    protected function dispatchMentionEvents($comment, $body)
    {
        $users = User::find($this->getMentionIds($body));

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
