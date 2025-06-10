<?php
declare(strict_types=1);

namespace Powernic\Bot\Framework\Attribute;

use Powernic\Bot\Framework\Handler\Callback\CallbackHandler;

#[\Attribute(\Attribute::TARGET_CLASS)]
class AsCommandHandler
{
    /**
     * @param string $route
     * @param string|null $description
     * @param class-string<CallbackHandler>[] $children
     */
    public function __construct(
        public string $route,
        public ?string $description = null,
        public array $children = [],
        public int $priority = 0,
        public bool $showButton = false
    )
    {
    }
}
