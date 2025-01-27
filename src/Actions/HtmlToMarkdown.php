<?php

namespace Kirschbaum\FilamentComments\Actions;

use League\HTMLToMarkdown\HtmlConverter;

class HtmlToMarkdown
{
    public static function run(...$args)
    {
        return (new static)(...$args);
    }

    public function __invoke(string $html): string
    {
        $converter = new HtmlConverter();
        $markdown = $converter->convert($html);
        $markdown = $this->transformMentionsToMarkdown($markdown);

        return $markdown;
    }

    protected function transformMentionsToMarkdown(string $markdown): string
    {
        return preg_replace(
            '/<span class="mention" data-id="(.*?)" data-label="(.*?)" data-type="mention">@(.*?)<\/span>/',
            '*@$2*',
            $markdown
        );
    }
}
