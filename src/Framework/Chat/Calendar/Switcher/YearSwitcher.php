<?php
declare(strict_types=1);

namespace Powernic\Bot\Framework\Chat\Calendar\Switcher;

class YearSwitcher extends Switcher
{

    public function canSwitchToNext(): bool
    {
        $maxYear = $this->getMaxYear();
        $targetYear = $this->getCurrentValue();
        return $targetYear < $maxYear;
    }

    private function getMaxYear(): int
    {
        if($this->maxDate) {
            return (int)$this->maxDate->format('Y');
        }else{
            return $this->getCurrentYear() + 1;
        }
    }

    public function canSwitchToPrev(): bool
    {
        $minYear = $this->getCurrentYear();
        $targetYear = $this->getCurrentValue();
        return $targetYear > $minYear;
    }

    protected function getCurrentValue(): int
    {
        return (int)$this->targetDate->format('Y');
    }
}
