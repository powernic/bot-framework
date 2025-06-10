<?php

namespace Powernic\Bot\Framework\Handler\Resolver;

use Powernic\Bot\Framework\Bot\Types\Update;
use Powernic\Bot\Framework\Handler\Checkout\CheckoutHandler;
use Powernic\Bot\Framework\Handler\Checkout\CheckoutHandlerLoader;
use Symfony\Component\DependencyInjection\ContainerInterface;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Message;
use TelegramBot\Api\Types\Payments\Query\PreCheckoutQuery;

class CheckoutHandlerResolver extends HandlerResolver
{
    public function __construct(
        ContainerInterface $container,
        Client $client)
    {
        parent::__construct($container, $client);
    }

    public function resolve(): void
    {
        if ($this->container->has('handler.checkout.loader')) {

            $this->client->on(function (Update $update) {
                $message = $update->getMessage();
                if(!$message) {
                    return true;
                }
                if ($message->getSuccessfulPayment()) {
                    $payment = $message->getSuccessfulPayment();

                    $handler = $this->resolveByPayload($payment->getInvoicePayload());
                    $handler->setSuccessful()
                        ->setMessage($message)
                        ->setUserId($message->getChat()->getId())
                        ->setTotalAmount($payment->getTotalAmount());
                    $this->setHandler($handler);
                    return false;
                }
                return true;
            }, function () {
                return true;
            });
            $this->client->preCheckoutQuery(
                function (PreCheckoutQuery $query) {
                    $handler = $this->resolveByPayload($query->getInvoicePayload());
                    if(!$handler) {
                        $this->client->answerPreCheckoutQuery(
                            preCheckoutQueryId: $query->getId(),
                            ok: false,
                            errorMessage: 'Invalid payload'
                        );
                        return;
                    }
                    $handler
                        ->setUserId($query->getFrom()->getId())
                        ->setPreCheckoutQueryId($query->getId())
                        ->setTotalAmount($query->getTotalAmount());
                    $this->setHandler($handler);
                }
            );
        }
    }

    private function resolveByPayload(string $name): ?CheckoutHandler
    {
        /** @var CheckoutHandlerLoader $handlerLoader */
        $handlerLoader = $this->container->get('handler.checkout.loader');
        $handlers = $handlerLoader->getNames();
        foreach ($handlers as $handlerName) {
            if ($handlerName === $name) {
                return $handlerLoader->get($handlerName);
            }
        }
        return null;
    }
}
