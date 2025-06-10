<?php
declare(strict_types=1);

namespace Powernic\Bot\Framework\Chat\Calendar\Selector;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Powernic\Bot\Framework\Chat\Calendar\Button;
use Powernic\Bot\Framework\Chat\Calendar\Selector\CallbackData\CallbackDataFactory;

abstract class Selector
{
    protected DateTime $selectedDate;

    public function __construct(
        protected CallbackDataFactory $callbackDataFactory,
        ?DateTime $selectedDate = null,
        private ?Selector $parentSelector = null,
        protected ?DateTime $maxDate = null
    ) {
        $this->selectedDate = is_null($selectedDate) ? new DateTime() : $selectedDate;
    }

    protected function getMaxYear(): ?int
    {
        return $this->maxDate ? (int)$this->maxDate->format('Y') : null;
    }

    protected function getMaxMonth(): ?int
    {
        return $this->maxDate ? (int)$this->maxDate->format('m') : 12;
    }

    protected function getMaxDay(): ?int
    {
        return $this->maxDate ? (int)$this->maxDate->format('d') : 31;
    }

    /**
     * @return  array<array<array{text:string,callback_data:string}>>
     */
    public function getButtons(): array
    {
        return array_merge(
            [$this->getHeaderButtons()],
            $this->getBodyButtons(),
            [$this->getFooterButtons()]
        );
    }

    abstract public function getMessage(): string;

    protected function getHeaderButtons(): array
    {
        return [];
    }

    abstract protected function getBodyButtons(): array;

    protected function getFooterButtons(): array
    {
        return [];
    }

    /**
     * @return Selector|null
     */
    public function getParentSelector(): ?Selector
    {
        return $this->parentSelector;
    }

    /**
     * @param array[] $matrix
     * @return array
     */
    protected function createButtonsFromMatrix(array $matrix): array
    {
        return (new ArrayCollection($matrix))->map(fn(array $texts) => $this->createButtons($texts))->toArray();
    }

    protected function createButtons(array $texts): array
    {
        $collection = (new ArrayCollection($texts));
        return (new ArrayCollection($collection->getKeys()))->map(
            fn(string $value) => $this->createButton($collection->get($value), (int)$value)
        )->toArray();
    }

    protected function createDisabledButtons(array $texts): array
    {
        $collection = (new ArrayCollection($texts));
        return (new ArrayCollection($collection->getKeys()))->map(
            fn(string $value) => $this->createButton($collection->get($value), 0)
        )->toArray();
    }

    /**
     * @param Button[] $buttons
     * @return bool
     */
    protected function allButtonsIsEmpty(array $buttons): bool
    {
        return (new ArrayCollection($buttons))->forAll(fn($key, Button $button) => $button->getValue() === 0);
    }

    /**
     * @param Button[] $buttons
     * @return array
     */
    protected function createLineButtons(array $buttons): array
    {
        return (new ArrayCollection($buttons))->map(
            fn(Button $button) => $this->createButton($button->getText(), $button->getValue())
        )->toArray();
    }

    protected function createButton(string $text, int $value): array
    {
        return ['text' => $text, 'callback_data' => $this->callbackDataFactory->create($value)];
    }
}
