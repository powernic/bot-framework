<?php
declare(strict_types=1);

namespace Powernic\Bot\Framework\Handler\Middleware;

use Powernic\Bot\Framework\Handler\HandlerInterface;

class MiddlewareStack implements StackInterface, MiddlewareInterface
{
    private int $position = 0;

    public function __construct(private array $middlewares = [])
    {
    }


    #[\Override]
    public function next(): MiddlewareInterface
    {
        if (!isset($this->middlewares[$this->position])) {
            return $this;
        }
        $middleware = $this->middlewares[$this->position];
        $this->position++;
        return $middleware;
    }

    #[\Override] public function handle(HandlerInterface $handler, StackInterface $stack): ?HandlerInterface
    {
        return $handler;
    }
}
