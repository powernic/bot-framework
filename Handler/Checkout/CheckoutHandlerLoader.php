<?php
declare(strict_types=1);

namespace Powernic\Bot\Framework\Handler\Checkout;

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Exception\CommandNotFoundException;

final class CheckoutHandlerLoader implements ContainerInterface
{

    /**
     * @param array $handlerMap An array with command names as keys and service ids as values
     */
    public function __construct(
        private ContainerInterface $container,
        private array $handlerMap )
    {

    }

    /**
     * @param string $id
     */
    public function get(string $id): CheckoutHandler
    {
        if (!$this->has($id)) {
            throw new CommandNotFoundException(sprintf('Command Handler "%s" does not exist.', $id));
        }

        return $this->container->get($this->handlerMap[$id]);
    }


    /**
     * {@inheritdoc}
     */
    public function has(string $id): bool
    {
        return isset($this->handlerMap[$id]) && $this->container->has($this->handlerMap[$id]);
    }


    /**
     * @return string[] All registered command names
     */
    public function getNames(): array
    {
        return array_keys($this->handlerMap);
    }

    public function getRefs(): array
    {
        return array_values($this->handlerMap);
    }
}
