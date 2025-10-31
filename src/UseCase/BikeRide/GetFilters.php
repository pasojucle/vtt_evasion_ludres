<?php

declare(strict_types=1);

namespace App\UseCase\BikeRide;

use App\Entity\BikeRide;
use DateInterval;
use DateTime;
use DateTimeImmutable;

class GetFilters
{
    public function execute(string $period, DateTimeImmutable $date, ?int $direction = null)
    {
        if (null !== $direction && BikeRide::PERIOD_MONTH === $period) {
            $date = $date->modify('first day of this month');

            if (BikeRide::DIRECTION_PREV === $direction) {
                $date = $date->sub(new DateInterval('P1M'));
            }
            if (BikeRide::DIRECTION_NEXT === $direction) {
                $date = $date->add(new DateInterval('P1M'));
            }
        }
        $startAt = clone $date;
        $endAt = clone $date;
        [$startAt, $endAt, $limit] = match ($period) {
            BikeRide::PERIOD_MONTH => [$date->modify('first day of this month'), $date->modify('last day of this month'), null],
            BikeRide::PERIOD_NEXT => [$date, null, 10],
            default => [null, null, null]
        };
        if (null !== $startAt) {
            $startAt = DateTime::createFromFormat('Y-m-d H:i:s', $startAt->format('Y-m-d') . ' 00:00:00');
        }
        if (null !== $endAt) {
            $endAt = DateTime::createFromFormat('Y-m-d H:i:s', $endAt->format('Y-m-d') . ' 23:59:59');
        }

        return [
            'startAt' => $startAt,
            'endAt' => $endAt,
            'period' => $period,
            'year' => $date->format('Y'),
            'month' => $date->format('m'),
            'day' => $date->format('d'),
            'date' => $date->format('y-m-d'),
            'limit' => $limit,
            'strMonth' => $this->getStringMonth((int) $date->format('m')),
        ];
    }

    private function getStringMonth(int $month): string
    {
        return [
           1 => 'Janvier',
           2 => 'Février',
           3 => 'Mars',
           4 => 'Avril',
           5 => 'Mai',
           6 => 'Juin',
           7 => 'Juillet',
           8 => 'Août',
           9 => 'Septembre',
           10 => 'Octobre',
           11 => 'Novembre',
           12 => 'Décembre'
        ][$month];
    }
}
