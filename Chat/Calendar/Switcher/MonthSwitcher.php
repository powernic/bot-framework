<?php
declare(strict_types=1);

namespace Powernic\Bot\Framework\Chat\Calendar\Switcher;

use DateTime;

class MonthSwitcher extends Switcher
{

    protected function getCurrentValue(): int
    {
        return (int)$this->targetDate->format('m');
    }

    public function canSwitchToNext(): bool
    {
        if($this->maxDate) {
            $maxYear =  (int)$this->maxDate->format('Y');
            $maxMonth = (int)$this->maxDate->format('m');
            $targetYear = (int)$this->targetDate->format('Y');
            $targetMonth = (int)$this->targetDate->format('m');
            return $targetYear < $maxYear || ($targetYear == $maxYear && $targetMonth < $maxMonth);
        }
        $maxYear = $this->getCurrentYear() + 1;
        $maxDate = DateTime::createFromFormat("d/m/Y", "1/12/{$maxYear}");
        return $this->targetDate < $maxDate;
    }

    public function canSwitchToPrev(): bool
    {
        return $this->targetDate > $this->currentDate;
    }
}
