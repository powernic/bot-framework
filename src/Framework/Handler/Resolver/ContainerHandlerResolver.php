<?php

namespace Powernic\Bot\Framework\Handler\Resolver;

use Exception;
use Powernic\Bot\Framework\Exception\UnResolvedHandlerException;
use Powernic\Bot\Framework\Handler\HandlerInterface;
use Psr\Log\LoggerInterface;

class ContainerHandlerResolver
{
    /**
     * @param HandlerResolver[] $handlerResolvers
     */
    public function __construct(
        private array $handlerResolvers,
        private LoggerInterface $logger)
    {
    }

    public function getHandlerResolver(string $className): ?HandlerResolver
    {
        foreach ($this->handlerResolvers as $handlerResolver) {
            if ($handlerResolver::class === $className) {
                return $handlerResolver;
            }
        }
        return null;
    }

    public function resolve(): void
    {
        $body = file_get_contents('php://input');
        if($body){
            $this->logger->info('Telegram request', ['body' => json_decode($body, true)]);
        }
        foreach ($this->handlerResolvers as $handlerResolver) {
            $handlerResolver->resolve();
        }
    }

    /**
     * @return HandlerInterface
     * @throws UnResolvedHandlerException
     */
    public function getHandler(): HandlerInterface
    {
        foreach ($this->handlerResolvers as $handlerResolver) {
            if ($handlerResolver->hasHandler()) {
                return $handlerResolver->getHandler();
            }
        }
        throw new UnResolvedHandlerException();
    }
}
