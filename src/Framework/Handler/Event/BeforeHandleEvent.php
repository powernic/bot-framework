<?php
declare(strict_types=1);

namespace Powernic\Bot\Framework\Handler\Event;

use Powernic\Bot\Framework\Handler\Handler;
use Symfony\Contracts\EventDispatcher\Event;

class BeforeHandleEvent extends Event
{

    public const NAME = 'bot.handler.before_handle';


    public function __construct(private Handler $handler)
    {
    }

    public function getHandler(): Handler
    {
        return $this->handler;
    }
}
