<?php
declare(strict_types=1);

namespace Powernic\Bot\Framework\Chat\Calendar\Switcher;

use DateTime;

abstract class Switcher
{

    public function __construct(
        protected DateTime $targetDate,
        protected DateTime $currentDate,
        protected ?DateTime $maxDate = null)
    {
    }

    public function createPrevButton(): SwitchButton
    {
        if ($this->canSwitchToPrev()) {
            $previousValue = $this->getPreviousValue();
            return new SwitchButton("<<", $previousValue);
        } else {
            return $this->createEmptyButton();
        }
    }


    public function createNextButton(): SwitchButton
    {
        if ($this->canSwitchToNext()) {
            $nextValue = $this->getNextValue();
            return new SwitchButton(">>", $nextValue);
        } else {
            return $this->createEmptyButton();
        }
    }

    abstract protected function getCurrentValue(): int;

    private function createEmptyButton(): SwitchButton
    {
        return new SwitchButton(" ", $this->getCurrentValue());
    }

    protected function getCurrentYear(): int
    {
        return (int)$this->currentDate->format('Y');
    }

    abstract public function canSwitchToNext(): bool;

    abstract public function canSwitchToPrev(): bool;

    private function getPreviousValue(): int
    {
        return $this->getCurrentValue() - 1;
    }

    private function getNextValue(): int
    {
        return $this->getCurrentValue() + 1;
    }
}
