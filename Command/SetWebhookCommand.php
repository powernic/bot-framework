<?php
declare(strict_types=1);

namespace Powernic\Bot\Framework\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TelegramBot\Api\BotApi;

#[AsCommand(
    name: 'bot:webhook:set',
    description: 'Set bot webhook to the specified URL'
)]
class SetWebhookCommand extends Command
{

    public function __construct(private BotApi $bot, private string $hookUrl)
    {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        $this->addArgument('hookUrl', InputArgument::OPTIONAL, 'Webhook URL');
        $this->addArgument('ipAddress', InputArgument::OPTIONAL, 'IP address');
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $hookUrl = $input->getArgument('hookUrl');
        $ipAddress = $input->getArgument('ipAddress');
        if ($hookUrl === null) {
            $hookUrl = $this->hookUrl;
        }
        try {
            $this->bot->setWebhook(url: $hookUrl, ipAddress: $ipAddress);
        } catch (\JsonMapper_Exception $e) {
            return Command::FAILURE;
        }
        $output->writeln('Webhook set successfully');
        return Command::SUCCESS;
    }
}
