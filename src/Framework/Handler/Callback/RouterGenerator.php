<?php
declare(strict_types=1);

namespace Powernic\Bot\Framework\Handler\Callback;

use Powernic\Bot\Framework\Attribute\AsCallbackHandler;

class RouterGenerator
{
    /**
     * @param class-string<CallbackHandler> $class
     * @param array<string,string> $options
     * @return string
     * @throws \ReflectionException
     */
    public function generate(
        string $class,
        array $options,
        bool $useContext = true,
        ?int $page = null): string
    {
        $reflection = new \ReflectionClass($class);
        $arguments = $reflection->getAttributes(AsCallbackHandler::class)[0]->getArguments();
        $contextRoute = null;
        if ($useContext) {
            $contextRoute = $arguments['contextRoute'] ?? null;
        }
        $route = $contextRoute ?: $arguments['route'];
        if($page !== null) {
            $route = '';
        }
        if (empty($options)) {
            return $route;
        }
        $routeWithOptions = $this->setOptionsToRoute($route, $options);
        if (!empty($page)) {
            $routeWithOptions = 'page:' . $page;
        }
        return $routeWithOptions;
    }

    /**
     *
     */
    private function setOptionsToRoute(string $route, $options): string
    {
        foreach ($options as $key => $value) {
            $route = str_replace('{' . $key . '}', (string)$value, $route);
        }
        return $route;
    }
}
