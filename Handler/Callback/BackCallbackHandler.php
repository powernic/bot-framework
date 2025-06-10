<?php
declare(strict_types=1);

namespace Powernic\Bot\Framework\Handler\Callback;

use Powernic\Bot\Framework\Attribute\AsCallbackHandler;
use Powernic\Bot\Framework\Handler\Resolver\CallbackHandlerResolver;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCallbackHandler(
    route: ".*?:back",
    contextRoute: "back",
    description: "Назад")]
class BackCallbackHandler extends CallbackHandler
{
    public function __construct(
        #[Autowire(service: 'handler_resolver.callback')] private CallbackHandlerResolver $handlerResolver)
    {
    }

    public function handle(): void
    {
        $contextService = new CallbackContextService($this->message);
        $context = $contextService->getContext();
        $handler = $this->handlerResolver->matchHandler($context['backRoute'], $this->message);
        $handler->setRoute($context['backRoute']);
        $handler->handle();
    }
}
