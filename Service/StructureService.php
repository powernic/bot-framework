<?php
declare(strict_types=1);

namespace Powernic\Bot\Framework\Service;

use Powernic\Bot\Framework\Attribute\AsCallbackHandler;
use Powernic\Bot\Framework\Attribute\AsCommandHandler;
use Powernic\Bot\Framework\Handler\Callback\CallbackHandler;
use Powernic\Bot\Framework\Handler\Command\CommandHandler;
use ReflectionClass;
use Symfony\Component\DependencyInjection\ContainerInterface;

class StructureService
{
    public function __construct(
        private ContainerInterface $container)
    {
    }


    /**
     * @param ReflectionClass $ref
     * @param string $attribute
     * @return bool
     */
    public function hasAttribute(ReflectionClass $ref, string $attribute): bool
    {
        return $ref->getAttributes($attribute) !== [];
    }

    /**
     * @param ReflectionClass<CallbackHandler> $ref
     * @return AsCallbackHandler
     * @throws \ReflectionException
     */
    public function getAttributeByCallbackRef(ReflectionClass $ref): AsCallbackHandler
    {
        return $ref->getAttributes(AsCallbackHandler::class)[0]->newInstance();
    }

    /**
     * @param ReflectionClass<CommandHandler> $ref
     * @return AsCommandHandler
     */
    public function getAttributeFromCommandRef(ReflectionClass $ref): AsCommandHandler
    {
        return $ref->getAttributes(AsCommandHandler::class)[0]->newInstance();
    }

    /**
     * @return ReflectionClass<CommandHandler>[] array
     */
    public function getCommandRefs(): array
    {
        $loader = $this->container->get('handler.command.loader');
        $refs = [];
        foreach ($loader->getRefs() as $class) {
            $ref = new \ReflectionClass($class);
            if (class_exists($class) && $this->hasAttribute($ref, AsCommandHandler::class)) {
                $refs[] = $ref;
            }
        }
        return $refs;
    }
}
