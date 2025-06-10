<?php
declare(strict_types=1);

namespace Powernic\Bot\Framework\Bot;

use Psr\Log\LoggerInterface;
use TelegramBot\Api\HttpException;
use Symfony\Contracts\Service\Attribute\Required;

class BotApi extends \TelegramBot\Api\BotApi
{

    private LoggerInterface $logger;

    private function reload(): void
    {
        curl_close($this->curl);
        $this->curl = curl_init();
    }

    #[Required]
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    #[\Override]
    public function call($method, ?array $data = null, $timeout = 10): mixed
    {
        $maxAttempts = 3;
        $attempts = 0;
        while ($attempts < $maxAttempts) {
            try {
                return parent::call($method, $data, $timeout);
            } catch (HttpException $e) {
                if ($e->getCode() === CURLE_COULDNT_CONNECT) {
                    $attempts++;
                    $this->reload();
                    $this->logger->error('Failed to send message. Attempt ' . $attempts);
                    if ($attempts === $maxAttempts) {
                        $this->logger->error('Failed to send message after ' . $maxAttempts . ' attempts');
                    }
                } else {
                    throw $e;
                }
            }
        }
    }


}
