<?php

namespace Powernic\Bot\Framework\Handler\Resolver;

use Powernic\Bot\Framework\Handler\HandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use TelegramBot\Api\Client;

abstract class HandlerResolver
{
    protected HandlerInterface $handler;
    protected RouteConverter $routeConverter;

    public function __construct(
        protected ContainerInterface $container,
        protected Client $client)
    {
        $this->routeConverter = new RouteConverter();
    }

    abstract public function resolve(): void;

    public function hasHandler(): bool
    {
        return isset($this->handler);
    }

    public function getHandler(): HandlerInterface
    {
        return $this->handler;
    }

    /**
     * @param HandlerInterface $handler
     */
    public function setHandler(HandlerInterface $handler): void
    {
        $this->handler = $handler;
    }

    protected function isValidHandlerParameters(string $callbackMask, string $route): bool
    {
        if ($callbackMask === $route) {
            return true;
        }
        $regex = $this->routeConverter->convertMaskToRegex($callbackMask);
        return preg_match($regex, $route) === 1;
    }

}
