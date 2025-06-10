<?php
declare(strict_types=1);

use Powernic\Bot\Framework\Chat\Button\ButtonFactory;
use Powernic\Bot\Framework\Handler\Callback\RouterGenerator;
use Powernic\Bot\Framework\Handler\Resolver\CallbackHandlerResolver;
use Powernic\Bot\Framework\Handler\Resolver\CheckoutHandlerResolver;
use Powernic\Bot\Framework\Handler\Resolver\TextHandlerResolver;
use Powernic\Bot\Framework\Handler\Resolver\CommandHandlerResolver;
use Powernic\Bot\Framework\Handler\Resolver\ContainerHandlerResolver;
use Powernic\Bot\Framework\Service\StructureService;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use TelegramBot\Api\BotApi;
use Powernic\Bot\Framework\Bot\BotApi as EnhancedBot;
use TelegramBot\Api\Client;
use Powernic\Bot\Framework\Bot\Client as EnhancedClient;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container) {
    $container->services()
        ->set(Client::class, EnhancedClient::class)
            ->public()
            ->arg('$token', '%env(TOKEN)%')
        ->set('handler_resolver.callback', CallbackHandlerResolver::class)
        ->args(
            [
                service('service_container'),
                service(Client::class),
            ]
        )
        ->set('handler_resolver.pre_checkout', CheckoutHandlerResolver::class)
        ->args(
            [
                service('service_container'),
                service(Client::class)
            ]
        )
        ->set('handler_resolver.command', CommandHandlerResolver::class)
            ->args(
                [
                    service('service_container'),
                    service(Client::class),
                ]
            )
        ->set('handler_resolver.text.callback', TextHandlerResolver::class)
        ->args(
            [
                service('service_container'),
                service(Client::class),
            ]
        )
        ->set('handler_resolver.text', TextHandlerResolver::class)
        ->args(
            [
                service('service_container'),
                service(Client::class),
            ]
        )
        ->set('handler_resolver.container', ContainerHandlerResolver::class)
            ->public()
            ->args(
                [
                    [
                        service('handler_resolver.pre_checkout'),
                        service('handler_resolver.callback'),
                        service('handler_resolver.command'),
                        service('handler_resolver.text.callback'),
                        service('handler_resolver.text'),
                        ],
                    service(LoggerInterface::class),
                ]
            )
        ->set(RouterGenerator::class, RouterGenerator::class)
        ->set(StructureService::class, StructureService::class)
            ->args([service('service_container')])
        ->set(ButtonFactory::class, ButtonFactory::class)
            ->args([service(RouterGenerator::class)])
        ->set(BotApi::class, EnhancedBot::class)
            ->public()
            ->arg('$token', '%env(TOKEN)%')
            ->autowire();
};
