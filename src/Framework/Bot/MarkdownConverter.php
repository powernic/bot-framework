<?php
declare(strict_types=1);

namespace Powernic\Bot\Framework\Bot;

class MarkdownConverter
{

    public function __construct(private string $markdown)
    {
    }

    private function convertBold(): self
    {
        $this->markdown = preg_replace('/\*{2}(.*?)\*{2}/', '*$1*', $this->markdown);
        return $this;
    }

    private function convertItalic( ): self
    {
        $this->markdown = preg_replace('/(?<!\*)\*(?!\*)(.*?)\*(?!\*)/', '_$1_', $this->markdown);
        return $this;
    }

    public function convert(): string
    {
        return $this
            ->convertItalic()
            ->convertBold()
            ->markdown;
    }
}
