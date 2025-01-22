<?php

namespace Kirschbaum\FilamentComments\Actions;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Kirschbaum\FilamentComments\Contracts\CommentAuthor;
use Kirschbaum\FilamentComments\Events\UserWasMentionedEvent;

class ParseComment
{
    public static function run(...$args)
    {
        return (new static)(...$args);
    }

    public function __invoke(string $body)
    {
        $body = $this->parseMentions($body);
        return $body;
    }

    protected function parseMentions(string $body)
    {
        return preg_replace_callback(
            // 1) Match <span>
            // 2) Containing class="... mention ..." (any order of classes)
            // 3) Containing data-type="mention"
            '/<span[^>]*class="[^"]*\bmention\b[^"]*"[^>]*data-type="mention"[^>]*>/i',
            function ($match) {
                $originalTag = $match[0];
                // dd($originalTag);

                // Inside that tag, find the class="..." portion and append " text-xs"
                // if "text-xs" is not already present.
                $updatedTag = preg_replace_callback(
                    '/class="([^"]*)"/i',
                    fn () => 'class="p-1 bg-gray-200 text-gray-600 rounded-lg"',
                    $originalTag,
                    1  // Replace only the first occurrence of class="...".
                );

                return $updatedTag;
            },
            $body
        );
    }
}
