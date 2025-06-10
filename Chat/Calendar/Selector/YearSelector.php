<?php

namespace Powernic\Bot\Framework\Chat\Calendar\Selector;

class YearSelector extends Selector
{
    protected function getBodyButtons(): array
    {
        $currentYear = date('Y');
        $nextYear = date('Y', strtotime('+1 year'));
        if($this->maxDate && $this->maxDate->format('Y') < $nextYear){
            return [$this->createButtons([$currentYear => $currentYear])];
        }
        return [$this->createButtons([$currentYear => $currentYear, $nextYear => $nextYear])];
    }

    public function getMessage(): string
    {
        return "Выберите год";
    }
}
