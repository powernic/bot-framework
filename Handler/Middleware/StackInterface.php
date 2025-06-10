<?php
declare(strict_types=1);

namespace Powernic\Bot\Framework\Handler\Middleware;

interface StackInterface
{
    public function next(): MiddlewareInterface;
}
