<?php

namespace Powernic\Bot\Framework\Handler;

use Powernic\Bot\Framework\Chat\Button\ButtonFactory;
use Powernic\Bot\Framework\Handler\Callback\CallbackHandler;
use Powernic\Bot\Framework\Handler\Event\BeforeHandleEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Service\Attribute\Required;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

abstract class Handler implements HandlerInterface
{
    protected BotApi $bot;
    protected ButtonFactory $buttonFactory;
    protected LoggerInterface $logger;
    protected bool $botAvailable = true;
    private EventDispatcherInterface $dispatcher;
    /**
     * @var class-string<CallbackHandler>
     */
    protected ?string $cancelHandler = null;

    #[Required]
    public function setBotAvailable(#[Autowire(env: 'bool:BOT_AVAILABLE')] bool $isAvailable = false): void
    {
        $this->botAvailable = $isAvailable;
    }

    #[Required]
    public function withDispatcher(EventDispatcherInterface $dispatcher): void
    {
        $this->dispatcher = $dispatcher;
    }

    public function beforeHandle(): void
    {
        $event = new BeforeHandleEvent($this);
        $this->dispatcher->dispatch($event, BeforeHandleEvent::NAME);
    }

    #[Required]
    public function withBot(BotApi $bot): void
    {
        $this->bot = $bot;
    }

    #[Required]
    public function withLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    #[Required]
    public function withButtonFactory(ButtonFactory $buttonFactory): void
    {
        $this->buttonFactory = $buttonFactory;
    }

    /**
     * class-string<CallbackHandler>
     */
    #[Required]
    public function withCancelHandler(
        #[Autowire('%cancelHandler%')] string $handler): void
    {
        $this->cancelHandler = $handler;
    }

    public function getUserId(): string
    {
        return (string) $this->message->getFrom()->getId();
    }

    protected function getButtons(): array
    {
        return [];
    }

    protected function sendMessage(
        string $message,
        array $buttons = [],
        bool $reply = false,
        bool $markdown = false): void
    {

        $chatId = $this->message->getChat()->getId();
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
            parseMode: $markdown ? 'markdown' : null,
            disablePreview: false,
            replyToMessageId: $reply ? $messageId : null,
            replyMarkup: new InlineKeyboardMarkup($buttons)
        );
    }


    protected function escapeMarkdown(string $message): string
    {
        return preg_replace('/([_*\[\]()~`>#\+\-=|{}.!])/', '\\\\$1', $message);
    }

    protected function forwardMessage(int $chatId, int $messageId): void
    {

        $fromChatId = $this->message->getChat()->getId();
        $messageId = $this->message->getMessageId();
        $this->logger->info(
            'Forward response',
            [
                'chatId' => $chatId,
                'fromChatId' => $fromChatId,
                'messageId' => $messageId,
            ]
        );
        $this->bot->forwardMessage(
            chatId: $chatId,
            fromChatId: $fromChatId,
            messageId: $messageId
        );
    }

    protected function getFooterButtons(): array
    {
        return [];
    }

    protected function createCancelButton(): array
    {
        return $this->createButton('✖ Отмена', $this->cancelHandler);
    }

    /**
     * @param string $text
     * @param class-string<HandlerInterface> $handler
     * @param array<string, string> $options
     * @return array[]
     * @throws \ReflectionException
     */
    protected function createButton(
        string $text,
        string $handler,
        array $options = [],
        bool $isRow = true,
        bool $useContext = true,
        ?int $page = null): array
    {
        return $this->buttonFactory->create(
            $text,
            $handler,
            $options,
            $isRow,
            $useContext,
            $page
        );
    }
}
