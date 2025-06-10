<?php
declare(strict_types=1);

namespace Powernic\Bot\Framework\Handler\Callback;

use Powernic\Bot\Framework\Attribute\AsCallbackHandler;
use ReflectionClass;
use Symfony\Contracts\Service\Attribute\Required;
use TelegramBot\Api\HttpException;
use Powernic\Bot\Framework\Handler\AvailableMessageInterface;
use Powernic\Bot\Framework\Handler\AvailableRouteInterface;
use Powernic\Bot\Framework\Handler\RouteHandler;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

abstract class CallbackHandler extends RouteHandler implements AvailableRouteInterface, AvailableMessageInterface
{
    private RouterGenerator $router;
    private array $contextMeta = [];

    #[Required]
    public function setRouter(RouterGenerator $router): void
    {
        $this->router = $router;
    }

    public function addContextMeta(string $key, int|string $value): void
    {
        $meta = $this->getContextMeta();
        $meta[$key] = $value;
        $this->setContextMeta($meta);
    }

    public function setContextMeta(array $meta): void
    {
        $this->contextMeta = $meta;
    }

    protected function getContextMetaByKey(string $key): mixed
    {
        return $this->getContextMeta()[$key] ?? null;
    }

    protected function getContextMeta(): array
    {
        if(empty($this->contextMeta)){
            $contextService = new CallbackContextService($this->message);
            $context = $contextService->getContext();
            return $context['meta'] ?? [];
        }
        return $this->contextMeta;
    }

    protected function sendResponse(string $message, array $buttons = [], bool $useContext = false, ?string $parseMode = null): void
    {
        $chatId = $this->message->getChat()->getId();
        $messageId = $this->message->getMessageId();
        $currentContext = new CallbackPrefixer($this->message);
        $backRoute = null;
        if ($this->hasContext()) {
            $backRoute = $this->getBackRoute();
            $route = $this->getRoute();
        } elseif ($currentContext->getPrefix()) {
            if (json_validate($currentContext->getPrefix())) {
                $context = json_decode($currentContext->getPrefix(), true);
                $route = $context['route'];
            } else {
                $route = $currentContext->getPrefix();
            }
        } else {
            $route = $this->getRoute();
        }
        $contextData = ['route' => $route];
        if ($backRoute) {
            $contextData['backRoute'] = $backRoute;
        }
        if (!empty($this->getContextMeta())) {
            $contextData['meta'] = $this->getContextMeta();
        }
        $context = CallbackPrefixer::encodePrefix(json_encode($contextData));
        $footerButtons = $this->getFooterButtons();
        if (!empty($footerButtons)) {
            $buttons[] = $this->getFooterButtons();
        }
        $this->logger->info(
            'Send response',
            [
                'chatId' => $chatId,
                'messageId' => $messageId,
                'message' => $message,
                'route' => $route,
                'context' => $contextData,
                'buttons' => $buttons
            ]
        );
        $maxAttempts = 3;
        $attempts = 0;
        if($parseMode === null) {
            $parseMode = ($this->hasContext() || $useContext) ? 'HTML' : 'MarkdownV2';
        }
        if ($parseMode === 'MarkdownV2') {
            $message = $this->escapeMarkdown($message);
        }
        while ($attempts < $maxAttempts) {
            try {
                $this->bot->editMessageText(
                    $chatId,
                    $messageId,
                    $message . (($this->hasContext() || $useContext) ? $context : ""),
                    $parseMode
                );
                if (!empty($buttons)) {
                    $this->bot->editMessageReplyMarkup($chatId, $messageId, new InlineKeyboardMarkup($buttons));
                }
                break;
            } catch (HttpException $e) {
                if ($e->getCode() === CURLE_COULDNT_CONNECT) {
                    $attempts++;
                    $this->logger->error('Failed to send message. Attempt ' . $attempts);
                    if ($attempts === $maxAttempts) {
                        $this->logger->error('Failed to send message after ' . $maxAttempts . ' attempts');
                    }
                } else {
                    throw $e;
                }
            }
        }
    }

    protected function hasContext(): bool
    {
        $ref = new ReflectionClass($this);
        $attributes = $ref->getAttributes(AsCallbackHandler::class);
        if (empty($attributes)) {
            return false;
        }

        $arguments = $attributes[0]->getArguments();
        if (!isset($arguments['startContext']) && !isset($arguments['contextRoute'])) {
            return false;
        }
        if (isset($arguments['startContext'])) {
            return $arguments['startContext'];
        }
        return true;
    }

    protected function getFooterButtons(): array
    {
        $ref = new ReflectionClass($this);
        $attributes = $ref->getAttributes(AsCallbackHandler::class);
        if (empty($attributes)) {
            return [];
        }
        /** @var AsCallbackHandler $attribute */
        $attribute = $attributes[0]->newInstance();
        if (empty($attribute->parent)) {
            return [];
        }
        return [...$this->createBackButton(), ...$this->createCancelButton()];
    }

    protected function createBackButton(): array
    {
        if ($this->hasContext()) {
            return $this->createButton('⬅️ Назад', BackCallbackHandler::class);
        }

        $handler = $this->getParentHandler();
        if ($handler === null) {
            return [];
        }
        $options = $this->getBackButtonOptions();

        return $this->createButton('⬅️ Назад', $handler, $options);
    }

    /**
     * @return class-string<CallbackHandler>
     */
    private function getParentHandler(): ?string
    {
        $ref = new ReflectionClass($this);
        /** @var AsCallbackHandler $attribute */
        $attribute = $ref->getAttributes(AsCallbackHandler::class)[0]->newInstance();
        if (!$attribute->parent) {
            return null;
        }
        return $attribute->parent;
    }

    protected function getBackRoute(): ?string
    {
        $handler = $this->getParentHandler();
        if ($handler === null) {
            return null;
        }
        $options = $this->getBackButtonOptions();
        return $this->router->generate($handler, $options, false);

    }

    protected function getBackButtonOptions(): array
    {
        $ref = new ReflectionClass($this);
        /** @var AsCallbackHandler $attribute */
        $attribute = $ref->getAttributes(AsCallbackHandler::class)[0]->newInstance();
        $options = $this->getOptionsByParent($attribute->parentOptions);
        if (empty($options)) {
            $options = $this->getOptionsByRoute($attribute->route);
        }
        return $options;
    }

    private function getOptionsByParent(array $options): array
    {
        $newOptions = [];
        foreach ($options as $key => $value) {
            $newOptions[$key] = $this->getParameter($value);
        }
        return $newOptions;
    }

    private function getOptionsByRoute(string $route): array
    {
        $matches = [];
        preg_match_all('/\{([^}]+)\}/', $route, $matches);
        $options = [];
        foreach ($matches[1] as $key) {
            $value = $this->getParameter($key);
            $options[$key] = $value;
        }
        return $options;
    }
}
