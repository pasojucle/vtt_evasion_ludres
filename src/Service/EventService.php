<?php

namespace App\Service;

use DateTime;
use DateInterval;
use App\Entity\Event;

class EventService
{
    public function getFiltersByParam(?string $period, ?int $year, ?int $month, ?int $day) {
        $date = (null === $year && null === $month && null === $day) ? new DateTime(): DateTime::createFromFormat('Y-m-d', "$year-$month-$day");
        if (null === $period) {
            $period = Event::PERIOD_WEEK;
        }

        return $this->getFilters($period, $date);
    }

    public function getFiltersByData(array $data) {
        $period = $data['period'];
        $date = new DateTime($data['date']);
        $direction = (array_key_exists('direction', $data)) ? $data['direction'] : null;

        return $this->getFilters($period, $date, $direction);
    }

    private function getFilters(string $period, DateTime $date, ?int $direction = null) {
        if (null !== $direction && Event::PERIOD_ALL !== $period) {
            $intervals = [
                Event::PERIOD_DAY => "P1D",
                Event::PERIOD_WEEK => "P1W",
                Event::PERIOD_MONTH => "P1M1D",
            ];
            if (Event::DIRECTION_PREV === $direction) {
                $date->sub(new DateInterval($intervals[$period]));
            }
            if (Event::DIRECTION_NEXT === $direction) {
                $date->add(new DateInterval($intervals[$period]));
            }
        }

        switch ($period) {
            case Event::PERIOD_DAY:
                $stardAt = $date;
                $endAt = $date;
                break;
            
            case Event::PERIOD_WEEK:
                $stardAt =  clone $date->modify('monday this week');
                $endAt = clone $date->modify('sunday this week');
                break;
            
            case Event::PERIOD_MONTH:
                $stardAt =  clone $date->modify('first day of this month');
                $endAt = $date->modify('last day of this month');
                break;
            
            default:
                $stardAt = null;
                $endAt = null;
        }
        if (null !== $stardAt && null !== $endAt) {
            $stardAt =  DateTime::createFromFormat('Y-m-d H:i:s', $stardAt->format('Y-m-d').' 00:00:00');
            $endAt =  DateTime::createFromFormat('Y-m-d H:i:s', $endAt->format('Y-m-d').' 23:59:59');
        }

        return ['startAt' => $stardAt, 'endAt' => $endAt, 'period' => $period, 'date' => $date->format('Y-m-d')];
    }
}