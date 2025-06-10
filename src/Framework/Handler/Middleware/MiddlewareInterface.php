<?php
declare(strict_types=1);

namespace Powernic\Bot\Framework\Handler\Middleware;

use Powernic\Bot\Framework\Handler\HandlerInterface;

interface MiddlewareInterface
{
    public function handle(HandlerInterface $handler, StackInterface $stack): ?HandlerInterface;
}
