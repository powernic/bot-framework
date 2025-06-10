<?php
declare(strict_types=1);

namespace Powernic\Bot\Framework\Handler\Command;

use Powernic\Bot\Framework\Attribute\AsCallbackHandler;
use Powernic\Bot\Framework\Handler\AvailableMessageInterface;
use Powernic\Bot\Framework\Handler\Handler;
use Powernic\Bot\Framework\Service\StructureService;
use ReflectionClass;
use Symfony\Contracts\Service\Attribute\Required;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use TelegramBot\Api\Types\Message;

abstract class CommandHandler extends Handler implements AvailableMessageInterface
{
    protected Message $message;
    private StructureService $structureService;

    #[Required]
    public function withStructureService(StructureService $structureService): void
    {
        $this->structureService = $structureService;
    }

    /**
     * @param Message $message
     * @return self
     */
    public function setMessage(Message $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getMessage(): Message
    {
        return $this->message;
    }


    public function sendResponse(string $message): void
    {

        $buttons = $this->getButtons();
        $buttons[] = $this->getFooterButtons();
        $keyboard = new InlineKeyboardMarkup($buttons);
        $this->bot->sendMessage(
            $this->message->getChat()->getId(),
            $message,
            null,
            false,
            null,
            $keyboard
        );
    }

    protected function getFooterButtons(): array
    {
        return $this->createCancelButton();
    }

    protected function getCommandButtons(): array
    {
        $buttonsQueue = [];
        foreach ($this->structureService->getCommandRefs() as $ref) {
            $attribute = $this->structureService->getAttributeFromCommandRef($ref);
            if (!$attribute->showButton) continue;
            $button = [['text' => $attribute->description]];
            $buttonsQueue[] = [
                'priority' => $attribute->priority,
                'button' => $button
            ];
        }
        usort($buttonsQueue, (fn(array $a, array $b) => $a['priority'] <=> $b['priority']));
        return array_map(fn(array $button) => $button['button'], $buttonsQueue);
    }
}
