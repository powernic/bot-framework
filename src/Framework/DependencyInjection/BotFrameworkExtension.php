<?php

namespace Powernic\Bot\Framework\DependencyInjection;

use Powernic\Bot\Framework\Attribute\AsCallbackHandler;
use Powernic\Bot\Framework\Attribute\AsCommandHandler;
use Powernic\Bot\Framework\Attribute\AsCheckoutHandler;
use Powernic\Bot\Framework\Attribute\AsTextHandler;
use Powernic\Bot\Framework\Handler\Checkout\CheckoutHandler;
use Powernic\Bot\Framework\Handler\Middleware\MiddlewareInterface;
use Powernic\Bot\Framework\Handler\Middleware\MiddlewareStack;
use Powernic\Bot\Framework\Repository\ServiceEntityRepositoryInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class BotFrameworkExtension extends Extension
{

    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new PhpFileLoader($container, new FileLocator(\dirname(__DIR__) . '/Resources/config'));

        $loader->load('services.php');

        $container->registerAttributeForAutoconfiguration(
            AsCallbackHandler::class,
            function (ChildDefinition $definition, AsCallbackHandler $attribute) {
                $definition->addTag('app.callback_handler', ['callback' => $attribute->route, 'context' => $attribute->contextRoute]);
            }
        );
        $container->registerAttributeForAutoconfiguration(
            AsCommandHandler::class,
            function (ChildDefinition $definition, AsCommandHandler $attribute) {
                $definition->addTag('app.command_handler', ['command' => $attribute->route, 'description' => $attribute->description]);
            }
        );
        $container->registerAttributeForAutoconfiguration(
            AsTextHandler::class,
            function (ChildDefinition $definition, AsTextHandler $attribute) {
                $definition->addTag('app.text_handler', ['action' => $attribute->route]);
            }
        );
        $container->registerAttributeForAutoconfiguration(
            AsCheckoutHandler::class,
            function (ChildDefinition $definition, AsCheckoutHandler $attribute) {
                $definition->addTag('app.checkout_handler', ['payload' => $attribute->payload]);
            }
        );
    }

}
