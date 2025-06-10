<?php
declare(strict_types=1);

namespace Powernic\Bot\Framework\Handler\Callback;

use TelegramBot\Api\Types\Message;
use TelegramBot\Api\Types\MessageEntity;

class CallbackContextService
{

    private static string $linkPrefix = 'tg://btn/';

    public function __construct(private ?Message $message = null)
    {
    }


    public function removeContextFromMessage(Message $message): void
    {
        $entities = $message->getEntities();
        foreach ($entities as $key => $entity) {
            if ($entity->getType() === 'text_link') {
                if (str_contains($entity->getUrl(), self::$linkPrefix)) {
                    unset($entities[$key]);
                    $message->setEntities($entities);
                }
            }
        }
    }

    public function hasContext(): bool
    {
        return $this->getRawContext() !== null;
    }

    public function createRoute(null|string $callbackData): ?string
    {
        if ($callbackData === null) {
            return null;
        }
        $route = $this->getContext()['route'];
        return $route . ':' . $callbackData;
    }

    /**
     * @return array<string,string|int|float>
     */
    public function getContext(): array
    {
        $context = $this->getRawContext();
        if ($context === null) {
            return [];
        }
        return json_decode($context, true);
    }

    private function getRawContext(): ?string
    {
        if ($this->message === null) {
            return null;
        }
        $entities = $this->message->getEntities();
        if (isset($entities)) {
            /** @var MessageEntity $entity */
            foreach ($entities as $entity) {
                if ($entity->getType() === 'text_link') {
                    if (str_contains($entity->getUrl(), self::$linkPrefix)) {
                        $encodedPrefix = str_replace(self::$linkPrefix, '', $entity->getUrl());
                        return base64_decode($encodedPrefix);
                    }
                }
            }
        }
        return null;
    }

}
