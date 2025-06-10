<?php
declare(strict_types=1);

namespace Powernic\Bot\Framework\Bot;

use Powernic\Bot\Framework\Bot\Types\Update;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Client as ClientBase;

class Client extends ClientBase
{
    /**
     * @inheritDoc
     */
    #[\Override] public function run()
    {
        if ($data = BotApi::jsonValidate((string) $this->getRawBody(), true)) {
            /** @var array $data */
            $this->handle([Update::fromResponse($data)]);
        }
    }
}
