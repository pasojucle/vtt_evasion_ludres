<?php

declare(strict_types=1);

namespace App\Mapper\BikeRide;

use App\Entity\BikeRide;
use DateTimeImmutable;
use IntlDateFormatter;

class BikeRidePeriodMapper
{
    public function mapToView(BikeRide $bikeRide): string
    {
        $startAt = $bikeRide->getStartAt();
        $endAt = $bikeRide->getEndAt();
        return  (null === $endAt)
            ? $this->formatDateLong($startAt)
            : $this->formatDateLong($startAt) . ' au ' . $this->formatDateLong($endAt);
    }

    private function formatDateLong(DateTimeImmutable $date): string
    {
        $formatter = new IntlDateFormatter('fr_fr', IntlDateFormatter::MEDIUM, IntlDateFormatter::NONE);
        $formatter->setPattern('EEEE d/M/yy');

        return ucfirst($formatter->format($date));
    }
}
