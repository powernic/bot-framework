<?php
declare(strict_types=1);

namespace Powernic\Bot\Framework\Chat\Calendar\Handler;

use DateTime;
use Powernic\Bot\Framework\Chat\Calendar\Selector\Selector;
use Powernic\Bot\Framework\Chat\Calendar\Selector\SelectorFactory;
use Powernic\Bot\Framework\Handler\Resolver\ContainerHandlerResolver;

class ExtendedCalendarCallbackHandler extends CalendarCallbackHandler
{
    public function __construct(
        ContainerHandlerResolver $containerHandlerResolver,
        SelectorFactory $selectorFactory
    ) {
        parent::__construct(
            $containerHandlerResolver,
            $selectorFactory
        );
    }

    #[\Override]
    protected function getSelector(string $calendarRoute): Selector
    {
        $maxYear = (int)$this->getParameter('maxYear');
        $maxMonth = (int)$this->getParameter('maxMonth');
        $maxDay = (int)$this->getParameter('maxDay');
        $maxDate = new DateTime("{$maxYear}-{$maxMonth}-{$maxDay}");

        $year = (int)$this->getParameter('year');
        $month = (int)$this->getParameter('month');
        $day = (int)$this->getParameter('day');
        return $this->selectorFactory->create($calendarRoute, $year, $month, $day, $maxDate);
    }
}
