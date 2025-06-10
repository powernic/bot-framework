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
    name: 'bot:get-me',
    description: 'Get bot information'
)]
class GetMeCommand extends Command
{

    public function __construct(private BotApi $bot)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $user = $this->bot->getMe();
        } catch (InvalidArgumentException $e) {
            $output->writeln('Invalid Argument Exception: ' . $e->getMessage());
            return Command::FAILURE;
        } catch (Exception $e) {
            $output->writeln('Exception: ' . $e->getMessage());
            return Command::FAILURE;
        }
        $output->writeln('Username: ' . $user->getUsername());
        $output->writeln('ID: ' . $user->getId());
        $output->writeln('First name: ' . $user->getFirstName());
        $output->writeln('Can join groups: ' . ($user->getCanJoinGroups() ? 'Yes' : 'No'));
        $output->writeln('Can read all group messages: ' . ($user->getCanReadAllGroupMessages() ? 'Yes' : 'No'));

        return Command::SUCCESS;
    }
}
