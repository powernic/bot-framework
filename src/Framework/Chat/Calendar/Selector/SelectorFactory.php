<?php
declare(strict_types=1);

namespace Powernic\Bot\Framework\Chat\Calendar\Selector;

use DateTime;
use Powernic\Bot\Framework\Chat\Calendar\Selector\CallbackData\DayDataFactory;
use Powernic\Bot\Framework\Chat\Calendar\Selector\CallbackData\DayPeriodDataFactory;
use Powernic\Bot\Framework\Chat\Calendar\Selector\CallbackData\MonthDataFactory;
use Powernic\Bot\Framework\Chat\Calendar\Selector\CallbackData\YearDataFactory;

class SelectorFactory
{
    public function create(
        string $calendarRoute,
        int $year = 0,
        int $month = 0,
        int $day = 0,
        ?DateTime $maxDate = null): Selector
    {
        if ($year && $month && $day) {
            return $this->createDayPeriodSelector($day, $month, $year, $calendarRoute);
        } elseif ($year && $month) {
            return $this->createDaySelector($month, $year, $calendarRoute, $maxDate);
        } elseif ($year) {
            return $this->createMonthSelector($year, $calendarRoute, $maxDate);
        } else {
            return $this->createYearSelector($calendarRoute, $maxDate);
        }
    }

    /**
     * @param int $month
     * @param int $year
     * @param string $calendarRoute
     * @return DaySelector
     */
    private function createDaySelector(int $month, int $year, string $calendarRoute, ?DateTime $maxDate = null): DaySelector
    {
        $date = DateTime::createFromFormat('d/m/Y', "01/{$month}/{$year}");
        $dataFactory = new DayDataFactory($calendarRoute, $date);
        $monthSelector = $this->createMonthSelector($year, $calendarRoute, $maxDate);
        return new DaySelector($dataFactory, $date, $monthSelector, $maxDate);
    }

    /**
     * @param int $year
     * @param string $calendarRoute
     * @return MonthSelector
     */
    private function createMonthSelector(int $year, string $calendarRoute, ?DateTime $maxDate = null): MonthSelector
    {
        $date = DateTime::createFromFormat('d/m/Y', "01/01/{$year}");
        $dataFactory = new MonthDataFactory($calendarRoute, $date);
        $yearSelector = $this->createYearSelector($calendarRoute, $maxDate);
        return new MonthSelector($dataFactory, $date, $yearSelector, $maxDate);
    }

    /**
     * @param int $day
     * @param int $month
     * @param int $year
     * @param string $calendarRoute
     * @return DayPeriodSelector
     */
    private function createDayPeriodSelector(int $day, int $month, int $year, string $calendarRoute): DayPeriodSelector
    {
        $date = DateTime::createFromFormat('d/m/Y', "{$day}/{$month}/{$year}");
        $dataFactory = new DayPeriodDataFactory($calendarRoute, $date);
        return new DayPeriodSelector($dataFactory, $date);
    }

    /**
     * @param string $calendarRoute
     * @param DateTime|null $maxDate
     * @return YearSelector
     */
    private function createYearSelector(string $calendarRoute, ?DateTime $maxDate = null): YearSelector
    {
        $dataFactory = new YearDataFactory(calendarRoute: $calendarRoute, maxDate: $maxDate);
        return new YearSelector($dataFactory, maxDate: $maxDate);
    }
}
