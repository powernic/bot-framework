<?php

namespace Powernic\Bot\Framework\Chat\Calendar\Selector\CallbackData;

use DateTime;

abstract class CallbackDataFactory
{
    protected string $callbackPrefix;
    protected DateTime $selectedDate;

    public function __construct(string $calendarRoute, ?DateTime $selectedDate = null, protected ?DateTime $maxDate = null)
    {
        $this->callbackPrefix = $this->createCallbackPrefix($calendarRoute);
        $this->selectedDate = is_null($selectedDate) ? new DateTime() : $selectedDate;
    }

    protected function createCallbackPrefix(string $calendarRoute): string
    {
        if($this->maxDate){
            return preg_replace('/:\d{1,2}-\d{1,2}-\d{1,4}:\d{1,4}:\d{1,2}:\d{1,2}:\d$/', '', $calendarRoute);
        }

        return preg_replace('/:\d{1,4}:\d{1,2}:\d{1,2}:\d$/', '', $calendarRoute);
    }

    protected function getYear(int $value): int
    {
        return 0;
    }

    protected function getMonth(int $value): int
    {
        return 0;
    }

    protected function getDay(int $value): int
    {
        return 0;
    }

    protected function getDayPeriod(int $value): int
    {
        return 0;
    }

    public function create(int $value): string
    {
        $year = $this->getYear($value);
        $month = $this->getMonth($value);
        $day = $this->getDay($value);
        $dayPeriod = $this->getDayPeriod($value);
        if($this->maxDate){
            $maxDate = $this->maxDate->format('d-m-Y');
            return $maxDate.":{$year}:{$month}:{$day}:{$dayPeriod}";
        }
        return "{$year}:{$month}:{$day}:{$dayPeriod}";
    }
}
