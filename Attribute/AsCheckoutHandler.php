<?php
declare(strict_types=1);

namespace Powernic\Bot\Framework\Attribute;


#[\Attribute(\Attribute::TARGET_CLASS)]
class AsCheckoutHandler
{
    /**
     * @param string $payload
     */
    public function __construct(
        public string $payload,
    )
    {
    }
}
