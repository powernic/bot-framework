<?php
declare(strict_types=1);

namespace Powernic\Bot\Framework\Handler\Command;

use Powernic\Bot\Framework\Handler\AvailableMessageInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Exception\CommandNotFoundException;

final class CommandHandlerLoader implements ContainerInterface
{

    /**
     * @param array $commandMap An array with command names as keys and service ids as values
     */
    public function __construct(
        private ContainerInterface $container,
        private array $commandMap,
        private array $commandDescriptionMap)
    {

    }

    /**
     * @param string $id
     * @return AvailableMessageInterface
     */
    public function get(string $id): AvailableMessageInterface
    {
        if (!$this->has($id)) {
            throw new CommandNotFoundException(sprintf('Command Handler "%s" does not exist.', $id));
        }

        return $this->container->get($this->commandMap[$id]);
    }

    public function getByDescription(string $description): AvailableMessageInterface
    {
        if (!$this->hasDescription($description)) {
            throw new CommandNotFoundException(sprintf('Command Handler "%s" does not exist.', $description));
        }

        return $this->container->get($this->commandDescriptionMap[$description]);
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $id): bool
    {
        return isset($this->commandMap[$id]) && $this->container->has($this->commandMap[$id]);
    }

    public function hasDescription(string $name): bool
    {
        return isset($this->commandDescriptionMap[$name]) && $this->container->has($this->commandDescriptionMap[$name]);
    }


    /**
     * @return string[] All registered command names
     */
    public function getNames(): array
    {
        return array_keys($this->commandMap);
    }

    public function getRefs(): array
    {
        return array_values($this->commandMap);
    }
}
