<?php
declare(strict_types=1);

namespace Powernic\Bot\Framework\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
class AsTextHandler
{
    public function __construct(
        public string $route,
        public ?string $description = null
    )
    {
    }
}
