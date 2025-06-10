<?php
declare(strict_types=1);

namespace Powernic\Bot\Framework;

use Powernic\Bot\Framework\DependencyInjection\CallbackHandlerPass;
use Powernic\Bot\Framework\DependencyInjection\CommandHandlerPass;
use Powernic\Bot\Framework\DependencyInjection\MiddlewarePass;
use Powernic\Bot\Framework\DependencyInjection\CheckoutHandlerPass;
use Powernic\Bot\Framework\DependencyInjection\TextHandlerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class BotFrameworkBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $container->addCompilerPass(new CommandHandlerPass());
        $container->addCompilerPass(new CallbackHandlerPass());
        $container->addCompilerPass(new CheckoutHandlerPass());
        $container->addCompilerPass(new MiddlewarePass());
        $container->addCompilerPass(new TextHandlerPass());
    }
}
