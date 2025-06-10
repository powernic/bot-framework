<?php
declare(strict_types=1);

namespace Powernic\Bot\Framework\Chat\Calendar\Selector;

use DateTime;
use Powernic\Bot\Framework\Chat\Calendar\Button;
use Powernic\Bot\Framework\Chat\Calendar\Switcher\YearSwitcher;

class MonthSelector extends Selector
{
    private DateTime $currentDate;

    private array $months = [
        1 => 'Янв',
        2 => 'Февр',
        3 => 'Мар',
        4 => 'Апр',
        5 => 'Май',
        6 => 'Июн',
        7 => 'Июл',
        8 => 'Авг',
        9 => 'Сент',
        10 => 'Окт',
        11 => 'Нояб',
        12 => 'Дек'
    ];

    protected function getBodyButtons(): array
    {
        $buttons = [];
        $monthsInRow = 3;
        $monthsInColumn = 4;
        $inactiveMonths = $this->getInactiveMonths();
        for ($rowIndex = 0; $rowIndex < $monthsInColumn; $rowIndex++) {
            $fromMonth = ($rowIndex * $monthsInRow) + 1;
            $toMonth = ($rowIndex + 1) * $monthsInRow;
            $lineButtons = $this->createMonthButtons(
                $inactiveMonths,
                $fromMonth,
                $toMonth
            );
            if (!$this->allButtonsIsEmpty($lineButtons)) {
                $buttons[] = $this->createLineButtons($lineButtons);
            }
        }
        return $buttons;
    }

    /**
     * @param int[] $inactiveMonths
     * @return Button[]
     */
    private function createMonthButtons(array $inactiveMonths, int $fromMonth, int $toMonth): array
    {
        return array_map(
            fn(int $monthNumber) => in_array($monthNumber, $inactiveMonths) ? new Button(" ", 0) : new Button(
                $this->months[$monthNumber],
                $monthNumber
            ),
            range($fromMonth, $toMonth)
        );
    }

    public function setCurrentDate(DateTime $date): void
    {
        $this->currentDate = $date;
    }

    public function getCurrentDate(): DateTime
    {
        if (!isset($this->currentDate)) {
            $this->currentDate = new DateTime();
        }
        return $this->currentDate;
    }

    private function getInactiveMonths(): array
    {
        $isTheSameYear = (int)$this->getCurrentDate()->format('Y') === (int)$this->selectedDate->format('Y');
        if ($isTheSameYear) {
            $endMonth = (int)$this->getCurrentDate()->format('m') - 1;
            if($endMonth < 1) {
                $months = [];
            }else{
                $months = range(1, $endMonth);
            }
            if ($this->maxDate) {
                if($this->getMaxMonth() < 12) {
                    $months = array_merge($months, range($this->getMaxMonth() + 1, 12));
                }
            }
            return $months;
        }
        return [];
    }

    protected function getFooterButtons(): array
    {
        $switcher = new YearSwitcher($this->selectedDate, new DateTime(), $this->maxDate);
        $selectedYear = $this->selectedDate->format('Y');
        $prevButton = $switcher->createPrevButton();
        $nextButton = $switcher->createNextButton();
        $yearSelector = $this->getParentSelector();
        return [
            $yearSelector->createButton($prevButton->getText(), $prevButton->getValue()),
            $yearSelector->createButton($selectedYear, 0),
            $yearSelector->createButton($nextButton->getText(), $nextButton->getValue()),
        ];
    }

    public function getMessage(): string
    {
        return "Выберите месяц";
    }
}
