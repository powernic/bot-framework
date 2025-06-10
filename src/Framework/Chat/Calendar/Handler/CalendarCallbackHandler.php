<?php
declare(strict_types=1);

namespace Powernic\Bot\Framework\Chat\Calendar\Handler;

use DateTime;
use DateTimeZone;
use Powernic\Bot\Framework\Chat\Calendar\Selector\DayPeriodEnum;
use Powernic\Bot\Framework\Chat\Calendar\Selector\Selector;
use Powernic\Bot\Framework\Chat\Calendar\Selector\SelectorFactory;
use Powernic\Bot\Framework\Handler\Callback\CallbackHandler;
use Powernic\Bot\Framework\Handler\Callback\CallbackPrefixer;
use Powernic\Bot\Framework\Handler\Resolver\CallbackHandlerResolver;
use Powernic\Bot\Framework\Handler\Resolver\ContainerHandlerResolver;

class CalendarCallbackHandler extends CallbackHandler
{

    public function __construct(
        protected ContainerHandlerResolver $containerHandlerResolver,
        protected SelectorFactory $selectorFactory
    ) {
    }

    public function handle(): void
    {
        if ($this->isFinish()) {
            $this->runTargetHandler();
        } else {
            $this->showCalendar();
        }
    }

    private function showCalendar()
    {
        $selector = $this->getSelector($this->getRoute());
        $this->sendResponse($selector->getMessage(), $selector->getButtons(), true);
    }

    private function getPrefix(): string
    {
        $prefixer = new CallbackPrefixer($this->message);
        return $prefixer->getPrefix();
    }

    protected function getSelector(string $calendarRoute): Selector
    {
        $year = (int)$this->getParameter('year');
        $month = (int)$this->getParameter('month');
        $day = (int)$this->getParameter('day');
        return $this->selectorFactory->create($calendarRoute, $year, $month, $day);
    }

    private function isFinish(): bool
    {
        $year = (int)$this->getParameter('year');
        $month = (int)$this->getParameter('month');
        $day = (int)$this->getParameter('day');
        $period = (int)$this->getParameter('period');
        if ($year && $month && $day && $period) {
            return true;
        }
        return false;
    }

    private function runTargetHandler(): void
    {
        /** @var CallbackHandlerResolver $handlerResolver */
        $handlerResolver = $this->containerHandlerResolver->getHandlerResolver(CallbackHandlerResolver::class);
        $context = $this->getPrefix();
        $route = $context;
        if (json_validate($context)) {
            $context = json_decode($context, true);
            $route = $context['route'];
        }
        $handler = $handlerResolver->matchHandler($route, $this->message);
        if ($handler instanceof DateIntervalHandlerInterface) {
            $year = (int)$this->getParameter('year');
            $month = (int)$this->getParameter('month');
            $day = (int)$this->getParameter('day');
            $period = (int)$this->getParameter('period');
            $startHour = 0;
            $endHour = 12;
            switch (DayPeriodEnum::from($period)) {
                case DayPeriodEnum::AM:
                    $startHour = 0;
                    $endHour = 12;
                    break;
                case DayPeriodEnum::PM:
                    $startHour = 12;
                    $endHour = 24;
                    break;
                case DayPeriodEnum::ALL:
                    $startHour = 0;
                    $endHour = 24;
                    break;
            }
            $start = DateTime::createFromFormat(
                'd/m/Y H',
                "{$day}/{$month}/{$year} {$startHour}",
                new DateTimeZone('Europe/Moscow')
            );
            $end = DateTime::createFromFormat(
                'd/m/Y H',
                "{$day}/{$month}/{$year} {$endHour}",
                new DateTimeZone('Europe/Moscow')
            );
            $handler->handleDateInterval($start, $end);
        }
    }
}
