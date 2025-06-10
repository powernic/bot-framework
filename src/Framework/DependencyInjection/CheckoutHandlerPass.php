<?php
declare(strict_types=1);

namespace Powernic\Bot\Framework\DependencyInjection;

use Powernic\Bot\Framework\Handler\Checkout\CheckoutHandler;
use Powernic\Bot\Framework\Handler\Command\CommandHandlerLoader;
use Powernic\Bot\Framework\Handler\Checkout\CheckoutHandlerLoader;
use Powernic\Bot\Framework\Handler\HandlerInterface;
use ReflectionException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\TypedReference;

class CheckoutHandlerPass implements CompilerPassInterface
{
    private string $tag;

    public function __construct(string $tag = 'app.checkout_handler')
    {
        $this->tag = $tag;
    }

    /**
     * @throws ReflectionException
     */
    public function process(ContainerBuilder $container): void
    {
        $commandHandlerServices = $container->findTaggedServiceIds($this->tag);
        $lazyCommandRefs = [];
        $lazyCommandMap = [];
        foreach ($commandHandlerServices as $id => $tags) {
            $definition = $container->getDefinition($id);
            $class = $container->getParameterBag()->resolveValue($definition->getClass());
            if (isset($tags[0]['payload'])) {
                $aliases = $tags[0]['payload'];
            } else {
                if (!$r = $container->getReflectionClass($class)) {
                    throw new InvalidArgumentException(
                        sprintf('Class "%s" used for service "%s" cannot be found.', $class, $id)
                    );
                }
                if (!$r->implementsInterface(HandlerInterface::class)) {
                    throw new InvalidArgumentException(
                        sprintf(
                            'The service "%s" tagged "%s" must be implements interface of "%s".',
                            $id,
                            $this->tag,
                            HandlerInterface::class
                        )
                    );
                }
                $aliases = $class::getDefaultName();
            }
            $aliases = explode('|', $aliases ?? '');
            $commandName = array_shift($aliases);
            $lazyCommandRefs[$id] = new TypedReference($id, $class);
            $lazyCommandMap[$commandName] = $id;
        }

        $container
            ->register('handler.checkout.loader', CheckoutHandlerLoader::class)
            ->setPublic(true)
            ->setArguments([ServiceLocatorTagPass::register($container, $lazyCommandRefs), $lazyCommandMap]);
    }
}
