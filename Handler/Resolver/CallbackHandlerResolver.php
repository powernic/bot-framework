<?php

namespace Powernic\Bot\Framework\Handler\Resolver;

use Powernic\Bot\Framework\Handler\Callback\CallbackContextService;
use Powernic\Bot\Framework\Handler\Callback\CallbackHandlerLoader;
use Powernic\Bot\Framework\Handler\HandlerInterface;
use TelegramBot\Api\Types\CallbackQuery;
use TelegramBot\Api\Types\Message;

class CallbackHandlerResolver extends HandlerResolver
{

    public function resolve(): void
    {
        $this->client->callbackQuery(
            function (CallbackQuery $callbackQuery) {
                $message = $callbackQuery->getMessage();
                $contextService = new CallbackContextService($message);
                $hasContext = $contextService->hasContext();

                $route = $callbackQuery->getData();
                if ($hasContext) {
                    $route = $contextService->createRoute($route);
                }
                $isPaged = $this->isPaged($route);
                $originalRoute = $route;
                if ($isPaged) {
                    $route = preg_replace('/:page:\d+$/', '', $route);
                }
                $handler = $this->matchHandler($route, $message, $hasContext);
                if($handler === null && $hasContext){
                    $contextService->removeContextFromMessage($message);
                    $handler = $this->matchHandler($callbackQuery->getData(), $message);
                }
                if($handler != null && $isPaged){
                    $handler->setPage($this->getPageFromRoute($originalRoute));
                }
                $this->setHandler($handler);
            }
        );
    }

    protected function getPageFromRoute(string $route): int
    {
        if (preg_match('/:page:(\d+)$/', $route, $matches)) {
            return (int)$matches[1];
        }
        return 1;
    }

    private function isPaged(string $route): bool
    {
        if (preg_match('/:page:\d+$/', $route)) {
           return true;
        }
        return false;
    }

    private function isRouteMatched(string $callbackMask, string $route): bool
    {
        return $callbackMask === $route || $this->isValidHandlerParameters($callbackMask, $route);
    }

    public function matchHandler(string $route, Message $message, bool $hasContext = false): ?HandlerInterface
    {
        if ($this->container->has('handler.callback.loader')) {
            $loaderId =  $hasContext ? 'handler.context_callback.loader' : 'handler.callback.loader';
            /** @var CallbackHandlerLoader $callbackHandlerLoader */
            $callbackHandlerLoader = $this->container->get($loaderId);
            $callbacks = $callbackHandlerLoader->getRefs();
            $matchedCallbackIds = [];
            foreach ($callbacks as $callbackId => $callbackHandler) {
                if ($this->isRouteMatched($callbackId, $route)) {
                    $matchedCallbackIds[] = $callbackId;
                }
            }
            //Support for old handlers
            if(empty($matchedCallbackIds)){
                return null;
            }
            $callbackId = $this->getPriorityCallback($matchedCallbackIds);
            $handler = $callbackHandlerLoader->get($callbackId);
            return $handler->setRoute($route)->setMessage($message);

        }
        return null;
    }


    /**
     * @param $callbackIds
     * @return string
     */
    private function getPriorityCallback($callbackIds = []): string
    {
        $priorityCallback = '';
        $priority = 100;
        foreach ($callbackIds as $callbackId) {
            $countParams = substr_count($callbackId, '{');
            if ($countParams < $priority) {
                $priority = $countParams;
                $priorityCallback = $callbackId;
            }
        }
        return $priorityCallback;
    }
}
