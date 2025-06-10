<?php
declare(strict_types=1);

namespace Powernic\Bot\Framework\Attribute;


use Powernic\Bot\Framework\Handler\Callback\CallbackHandler;

#[\Attribute(\Attribute::TARGET_CLASS)]
class AsCallbackHandler
{
    /**
     * @param class-string<CallbackHandler>[] $children
     * @param class-string<CallbackHandler>|null $parent
     * @param array<string, string> $parentOptions
     */
    public function __construct(
        public string $route,
        public ?string $contextRoute = null,
        public ?string $description = null,
        public array $children = [],
        public ?string $parent = null,
        public array $parentOptions = [],
        public bool $startContext = false,
    )
    {
    }
}
