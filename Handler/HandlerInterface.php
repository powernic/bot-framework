<?php

namespace Powernic\Bot\Framework\Handler;

interface HandlerInterface
{
    public function handle(): void;
    public function beforeHandle(): void;
}
