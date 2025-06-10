<?php
declare(strict_types=1);

namespace Powernic\Bot\Framework\DependencyInjection;
use Powernic\Bot\Framework\Handler\Middleware\MiddlewareStack;
use Powernic\Bot\Framework\Handler\Middleware\StackInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class MiddlewarePass implements CompilerPassInterface
{

    #[\Override] public function process(ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config  = (new Processor())->processConfiguration($configuration,
            $container->getExtensionConfig(Configuration::CONFIG_ROOT_KEY));

        $middlewares = $config['chat']['middlewares'] ?? [];
        $middlewareServices = [];
        foreach ($middlewares as $middleware) {
            $middlewareServices[] = new Reference($middleware);
        }
        $container->register(StackInterface::class, MiddlewareStack::class)
            ->setPublic(true)
            ->setArguments([$middlewareServices]);
    }

}
