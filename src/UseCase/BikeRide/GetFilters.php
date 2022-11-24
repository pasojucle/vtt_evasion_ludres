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
        if (null !== $direction && !in_array($period, [BikeRide::PERIOD_ALL, BikeRide::PERIOD_NEXT], true)) {
            $intervals = [
                BikeRide::PERIOD_DAY => 'P1D',
                BikeRide::PERIOD_WEEK => 'P1W',
                BikeRide::PERIOD_MONTH => 'P1M1D',
            ];

            if (BikeRide::DIRECTION_PREV === $direction) {
                $date = $date->sub(new DateInterval($intervals[$period]));
            }
            if (BikeRide::DIRECTION_NEXT === $direction) {
                $date = $date->add(new DateInterval($intervals[$period]));
            }
        }
        $startAt = clone $date;
        $endAt = clone $date;
        $limit = null;
        switch ($period) {
            case BikeRide::PERIOD_DAY:
                $startAt = $startAt;
                $endAt = $endAt;

                break;
            case BikeRide::PERIOD_WEEK:
                $startAt = $startAt->modify('monday this week');
                $endAt = $endAt->modify('sunday this week');

                break;
            case BikeRide::PERIOD_MONTH:
                $startAt = $startAt->modify('first day of this month');
                $endAt = $endAt->modify('last day of this month');

                break;
            case BikeRide::PERIOD_NEXT:
                $startAt = $startAt;
                $endAt = null;
                $limit = 10;

                break;
            default:
                $startAt = null;
                $endAt = null;
        }
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
