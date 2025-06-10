<?php

namespace Powernic\Bot\Framework\Handler\Resolver;

use Powernic\Bot\Framework\Bot\Types\ChatMemberUpdated;
use Powernic\Bot\Framework\Bot\Types\Update;
use Powernic\Bot\Framework\Handler\ChatGroupHandler;
use Powernic\Bot\Framework\Handler\ChatMemberHandler;
use TelegramBot\Api\Types\Message;

class TextHandlerResolver extends HandlerResolver
{
    public function resolve(): void
    {

        $this->client->on(function (Update $update) {
            $message = $update->getMessage();
            if ($message !== null) {
                if ($message->getChat()->getType() === 'group') {
                    $this->onGroupChatMessage($message);
                    return;
                }
                $this->onBotChatMessage($message);
                return;
            }
            $chatMember = $update->getMyChatMember();
            if ($chatMember !== null) {
                $this->onChatMember($chatMember);
                return;
            }
        }, function () {
            return true;
        });
    }

    private function onGroupChatMessage(Message $message)
    {
        if (!$this->container->has(ChatGroupHandler::class)) {
            return;
        }
        /** @var ChatGroupHandler $groupHandler */
        $groupHandler = $this->container->get(ChatGroupHandler::class);
        $groupHandler->setMessage($message);
        $this->setHandler($groupHandler);
    }

    private function onBotChatMessage(Message $message): void
    {
        if (!$this->container->has('handler.resolver.text')) {
            return;
        }
        /** @var TextHandlerResolverInterface $textHandlerResolver */
        $textHandlerResolver = $this->container->get('handler.resolver.text');
        $action = $textHandlerResolver->resolve($message);
        $handlerLoader = $this->container->get('handler.text.loader');
        $commandHandlerLoader = $this->container->get('handler.command.loader');
        $text = $message->getText();
        if ($text !== null && $this->isReservedCommand($text)) {
            $handler = $commandHandlerLoader->getByDescription($message->getText());
            $this->setHandler($handler->setMessage($message));
            return;
        }
        foreach ($handlerLoader->getNames() as $actionMask) {
            if ($this->isValidHandlerParameters($actionMask, $action)) {
                $handler = $handlerLoader->get($actionMask);
                $this->setHandler($handler->setMessage($message));
                return;
            }
        }
    }

    private function isReservedCommand(string $command): bool
    {
        $commandHandlerLoader = $this->container->get('handler.command.loader');
        if ($commandHandlerLoader->hasDescription($command)) {
            return true;
        }
        return false;
    }

    private function onChatMember(ChatMemberUpdated $chatMemberUpdated): void
    {

        if (!$this->container->has(ChatMemberHandler::class)) return;
        $chatMemberHandler = $this->container->get(ChatMemberHandler::class);
        $chatMemberHandler->setMember($chatMemberUpdated);
        $this->setHandler($chatMemberHandler);
    }
}
