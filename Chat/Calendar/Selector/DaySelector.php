<?php
declare(strict_types=1);

namespace Powernic\Bot\Framework\Chat\Calendar\Selector;

use DateTime;
use Powernic\Bot\Framework\Chat\Calendar\Button;
use Powernic\Bot\Framework\Chat\Calendar\Selector\CallbackData\CallbackDataFactory;
use Powernic\Bot\Framework\Chat\Calendar\Selector\CallbackData\DayDataFactory;
use Powernic\Bot\Framework\Chat\Calendar\Selector\CallbackData\MonthDataFactory;
use Powernic\Bot\Framework\Chat\Calendar\Switcher\MonthSwitcher;

class DaySelector extends Selector
{
    private array $daysOfWeekNames = ["П", "В", "С", "Ч", "П", "С", "В"];
    private array $monthNames = ['Янв', 'Февр', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сент', 'Окт', 'Нояб', 'Дек'];
    private DateTime $currentDate;

    public function getCurrentDate(): DateTime
    {
        if (!isset($this->currentDate)) {
            $this->currentDate = new DateTime();
        }
        return $this->currentDate;
    }

    public function setCurrentDate(DateTime $date): void
    {
        $this->currentDate = $date;
    }

    protected function getHeaderButtons(): array
    {
        return $this->createDisabledButtons($this->daysOfWeekNames);
    }

    protected function getBodyButtons(): array
    {
        $countsDaysInWeek = 7;
        $countDays = (int)$this->selectedDate->format('t');
        $month = $this->selectedDate->format('m');
        $year = $this->selectedDate->format('Y');
        $firstDayInMonth = DateTime::createFromFormat("d/m/Y", "1/{$month}/{$year}");
        $daysOfWeekOfFirstDayInMonth = (int)$firstDayInMonth->format('N');
        $lastDayInMonth = DateTime::createFromFormat("d/m/Y", "{$countDays}/{$month}/{$year}");
        $inactiveDays = $this->getInactiveDays();
        $daysOfWeekOfLastDayInMonth = (int)$lastDayInMonth->format('N');
        $countEmptyButtonsInFirstRow = $daysOfWeekOfFirstDayInMonth - 1;
        $countEmptyButtonsInLastRow = $countsDaysInWeek - $daysOfWeekOfLastDayInMonth;
        $gridCellCounts = $countEmptyButtonsInFirstRow + $countDays + $countEmptyButtonsInLastRow;
        $gridRowCounts = $gridCellCounts / $countsDaysInWeek;
        $buttons = [];
        $gridValues = array_merge(
            $this->createEmptyButtons($countEmptyButtonsInFirstRow),
            $this->createDayButtons($inactiveDays, $countDays),
            $this->createEmptyButtons($countEmptyButtonsInLastRow)
        );
        $valueIndex = 0;
        for ($gridRowIndex = 0; $gridRowIndex < $gridRowCounts; $gridRowIndex++) {
            $buttonsInline = [];
            for ($gridColumnIndex = 0; $gridColumnIndex < $countsDaysInWeek; $gridColumnIndex++) {
                $buttonsInline[] = $gridValues[$valueIndex];
                $valueIndex++;
            }
            if (!$this->allButtonsIsEmpty($buttonsInline)) {
                $lineButtons = $this->createLineButtons($buttonsInline);
                $buttons[] = $lineButtons;
            }
        }
        return $buttons;
    }

    /**
     * @param int[] $inactiveDays
     * @param int $count
     * @return array
     */
    private function createDayButtons(array $inactiveDays, int $count): array
    {
        return array_map(
            fn(int $day) => in_array($day, $inactiveDays) ? new Button(" ", 0) : new Button((string)$day, $day),
            range(1, $count)
        );
    }

    /**
     * @param int $count
     * @return Button[]
     */
    private function createEmptyButtons(int $count): array
    {
        if ($count < 1) {
            return [];
        }
        return array_map(fn() => new Button(' ', 0), range(1, $count));
    }

    protected function getFooterButtons(): array
    {
        $switcher = new MonthSwitcher($this->selectedDate, new DateTime(), $this->maxDate);
        $selectedYear = (int)$this->selectedDate->format('Y');
        $selectedMonthNumber = (int)$this->selectedDate->format('m');
        $selectedMonth = $this->monthNames[$selectedMonthNumber - 1];
        $prevButton = $switcher->createPrevButton();
        $nextButton = $switcher->createNextButton();
        $monthSelector = $this->getParentSelector();
        $yearSelector = $monthSelector->getParentSelector();
        return [
            $monthSelector->createButton($prevButton->getText(), $prevButton->getValue()),
            $yearSelector->createButton($selectedMonth . " " . $selectedYear, $selectedYear),
            $monthSelector->createButton($nextButton->getText(), $nextButton->getValue())
        ];
    }

    public function getMessage(): string
    {
        return "Выберите день";
    }

    /**
     * @return int[]
     */
    private function getInactiveDays(): array
    {
        $currentDate = $this->getCurrentDate();
        $isTheSameYear = (int)$currentDate->format('Y') === (int)$this->selectedDate->format('Y');
        $isTheSameMonth = (int)$currentDate->format('m') === (int)$this->selectedDate->format('m');
        if ($isTheSameYear && $isTheSameMonth) {
            $days = range(1, (int)$currentDate->format('d') - 1);
            $hasMaxDate = isset($this->maxDate);
            $isTheSameYearLikeMaxData = $hasMaxDate && (int)$currentDate->format('Y') === (int)$this->maxDate->format('Y');
            $isTheSameMonthLikeMaxData = $hasMaxDate && (int)$currentDate->format('m') === (int)$this->maxDate->format('m');
            if ($hasMaxDate && ($isTheSameYearLikeMaxData && $isTheSameMonthLikeMaxData)) {
                $days = array_merge($days, range($this->getMaxDay() + 1, (int)$this->selectedDate->format('t')));
            }
            return $days;
        }
        return [];
    }

}
