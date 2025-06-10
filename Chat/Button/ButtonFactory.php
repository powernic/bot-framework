<?php
declare(strict_types=1);

namespace Powernic\Bot\Framework\Chat\Button;

use Powernic\Bot\Framework\Handler\Callback\RouterGenerator;
use Powernic\Bot\Framework\Handler\HandlerInterface;

class ButtonFactory
{
    public function __construct(private RouterGenerator $router)
    {
    }

    /**
     * @param string $text
     * @param class-string<HandlerInterface> $handler
     * @param array<string, string> $options
     * @return array[]
     * @throws \ReflectionException
     */
    public function create(
        string $text,
        string $handler,
        array $options = [],
        bool $isRow = true,
        bool $useContext = true,
        ?int $page = null): array
    {
        $route = $this->router->generate($handler, $options, $useContext, $page);
        if ($isRow) {
            return [['text' => $text, 'callback_data' => $route]];
        }
        return ['text' => $text, 'callback_data' => $route];
    }
}
