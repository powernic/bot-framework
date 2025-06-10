<?php
declare(strict_types=1);

namespace Powernic\Bot\Framework\Handler;

use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use TelegramBot\Api\Types\Message;

abstract class ChatGroupHandler extends Handler implements AvailableMessageInterface
{
    protected Message $message;

    #[\Override] public function setMessage(Message $message): AvailableMessageInterface
    {
        $this->message = $message;
        return $this;
    }


    protected function sendMessage(string $message, array $buttons = [], bool $reply = false, bool $markdown = false, ?int $chatId = null, ?int $replyToMessageId = null): void
    {

        $chatId = $chatId ?: $this->message->getFrom()->getId();
        $messageId = $this->message->getMessageId();
        $this->logger->info(
            'Send response',
            [
                'chatId' => $chatId,
                'messageId' => $messageId,
                'message' => $message,
                'buttons' => $buttons
            ]
        );
        $this->bot->sendMessage(
            chatId: $chatId,
            text: $message,
            replyToMessageId: $replyToMessageId,
            replyMarkup: new InlineKeyboardMarkup($buttons)
        );
    }
}
