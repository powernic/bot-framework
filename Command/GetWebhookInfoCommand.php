<?php
declare(strict_types=1);

namespace Powernic\Bot\Framework\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Exception;
use TelegramBot\Api\InvalidArgumentException;

#[AsCommand(
    name: 'bot:webhook:info',
    description: 'Get current webhook info'
)]
class GetWebhookInfoCommand extends Command
{
    public function __construct(private BotApi $bot)
    {
        parent::__construct( );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Webhook info');
        try {
            $info = $this->bot->getWebhookInfo();
            $output->writeln('URL: ' . $info->getUrl());
            $output->writeln('Pending updates: ' . $info->getPendingUpdateCount());
            $output->writeln('Last error date: ' . $info->getLastErrorDate());
            $output->writeln('Last error message: ' . $info->getLastErrorMessage());
        } catch (InvalidArgumentException $e) {
        } catch (Exception $e) {
        }
        return Command::SUCCESS;
    }
}
