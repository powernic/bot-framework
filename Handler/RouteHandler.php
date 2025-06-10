<?php
declare(strict_types=1);

namespace Powernic\Bot\Framework\Handler;

use TelegramBot\Api\Types\Message;
use TelegramBot\Api\Types\Update;

abstract class RouteHandler extends Handler implements AvailableMessageInterface, AvailableRouteInterface
{
    private string $name;

    protected Update $update;
    private string $route;
    protected Message $message;
    protected int $page = 1;

    public function setMessage(Message $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getMessage(): Message
    {
        return $this->message;
    }


    public function setName(string $name)
    {
        $this->name = $name;
    }

    protected function getName(): string
    {
        return $this->name;
    }

    public function setRoute(string $route): self
    {
        $this->route = $route;

        return $this;
    }

    public function setPage(int $page): self
    {
        $this->page = $page;
        return $this;
    }

    protected function getRoute(): string
    {
        return $this->route;
    }

    /**
     * @return array<string,string>
     */
    protected function getParameters(): array
    {
        $parameters = [];
        $namePattern = $this->getNamePattern();

        if ($namePattern && preg_match($namePattern, $this->getRoute(), $matches)) {
            foreach ($matches as $key => $value) {
                if (is_string($key)) {
                    $parameters[$key] = $value;
                }
            }
        }

        return $parameters;
    }

    /**
     * Converts the handler name to a regex pattern for extracting parameters.
     * Caches the pattern to avoid repeated conversions.
     *
     * @return string|null The regex pattern or null if name does not contain parameters.
     */
    private function getNamePattern(): ?string
    {
        static $cache = [];

        $name = $this->getName();
        if (isset($cache[$name])) {
            return $cache[$name];
        }

        if (preg_match('/{.*?}/', $name)) {
            $clearedName = preg_replace('/\<.*?\>/', '', $name);
            $pattern = preg_replace('/{(.*?)}/', '(?P<$1>[^/]+)', $clearedName);
            $regexPattern = '/^' . str_replace('/', '\/', $pattern) . '$/';
            $cache[$name] = $regexPattern;
            return $regexPattern;
        }

        return null;
    }

    protected function getParameter(string $name): string
    {
        $parameters = $this->getParameters();

        return $parameters[$name];
    }
}
