<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\BikeRide;
use App\Twig\AppExtension;

class BikeRideService
{
    public function __construct(
        private readonly AppExtension $appExtension
    ) {
    }


    public function getPeriod(BikeRide $bikeRide): string
    {
        $startAt = $bikeRide->getStartAt();
        $endAt = $bikeRide->getEndAt();
        return  (null === $endAt)
            ? $this->appExtension->formatDateLong($startAt)
            : $this->appExtension->formatDateLong($startAt) . ' au ' . $this->appExtension->formatDateLong($endAt);
    }
}
