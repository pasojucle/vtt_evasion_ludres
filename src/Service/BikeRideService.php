<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\BikeRide;
use App\Entity\Level;
use App\Entity\Member;
use App\Twig\AppExtension;
use DateInterval;
use DateTimeImmutable;

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

    public function getDateTimePeriod(BikeRide $bikeRide): array
    {
        $startAt = $bikeRide->getStartAt()->setTime(0, 0, 0);
        $closingAt = $bikeRide->getEndAt() ?? $bikeRide->getStartAt();
        $interval = new DateInterval(sprintf('P%sD', $bikeRide->getDisplayDuration()));

        return [
            'displayAt' => $startAt->sub($interval),
            'startAt' => $startAt,
            'closingAt' => $closingAt->setTime(23, 59, 59),
        ];
    }
           
    public function registrationClosed(BikeRide $bikeRide, ?Member $member): bool
    {
        if (!$bikeRide->registrationEnabled()) {
            return true;
        }

        if ($bikeRide->getBikeRideType()->isNeedFramers() && Level::TYPE_ADULT_MEMBER === $member?->getLevel()->getType()) {
            return false;
        }

        $intervalClosing = new DateInterval('P' . $bikeRide->getClosingDuration() . 'D');
        $period = $this->getDateTimePeriod($bikeRide);
        $today = (new DateTimeImmutable())->setTime(0, 0, 0);

        return $period['closingAt']->sub($intervalClosing) < $today;
    }
}
