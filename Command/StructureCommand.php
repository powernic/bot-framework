<?php
declare(strict_types=1);

namespace Powernic\Bot\Framework\Command;

use Powernic\Bot\Framework\Attribute\AsCallbackHandler;
use Powernic\Bot\Framework\Attribute\AsCommandHandler;
use Powernic\Bot\Framework\Service\StructureService;
use ReflectionClass;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'bot:structure',
    description: 'Show bot structure')]
class StructureCommand extends Command
{
    public function __construct(
        private StructureService $structureService
    ) {
        parent::__construct();
    }

    #[\Override] protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Bot structure');
        $output->writeln('Command handlers:');
        foreach ($this->structureService->getCommandRefs() as $ref) {
            $attribute = $this->structureService->getAttributeFromCommandRef($ref);
            $output->writeln(sprintf('  /%s - %s', $attribute->route, $attribute->description));
            $this->showChildrenHandlers($attribute, $output, 2);
        }
        return Command::SUCCESS;
    }

    /**
     * @param AsCommandHandler|AsCallbackHandler $attribute
     * @param OutputInterface $output
     * @return void
     * @throws \ReflectionException
     */
    private function showChildrenHandlers(AsCommandHandler|AsCallbackHandler $attribute, OutputInterface $output, int $deep = 0): void
    {
        foreach ($attribute->children as $child) {
            $childRef = new \ReflectionClass($child);
            if (class_exists($child) && $this->structureService->hasAttribute($childRef, AsCallbackHandler::class)) {
                $childAttribute = $this->structureService->getAttributeByCallbackRef($childRef);
                $offset = str_repeat('  ', $deep);
                $output->writeln(sprintf('%s%s - %s', $offset, $childAttribute->route, $childAttribute->description));
                $this->showChildrenHandlers($childAttribute, $output, $deep + 1);
            }
        }
    }
}
