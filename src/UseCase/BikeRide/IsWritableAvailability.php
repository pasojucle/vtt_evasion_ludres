<?php

declare(strict_types=1);

namespace App\UseCase\BikeRide;

use App\Entity\BikeRide;
use App\Entity\Enum\RegistrationEnum;
use App\Entity\Level;
use App\Entity\User;
use App\Service\BikeRideService;
use DateTimeImmutable;
use Symfony\Bundle\SecurityBundle\Security;

class IsWritableAvailability
{
    public function __construct(
        private readonly Security $security,
        private readonly BikeRideService $bikeRideService,
    ) {
    }
    
    public function execute(BikeRide $bikeRide, ?User $user): bool
    {
        $bikeRideType = $bikeRide->getBikeRideType();
        if (RegistrationEnum::NONE === $bikeRideType->getRegistration()) {
            return false;
        }

        $today = (new DateTimeImmutable())->setTime(0, 0, 0);
        $dateTimerPeriod = $this->bikeRideService->getDateTimePeriod($bikeRide);
        if ($bikeRideType->isNeedFramers()) {
            return $this->security->isGranted('BIKE_RIDE_VIEW', $bikeRide) && Level::TYPE_FRAME === $user?->getLevel()?->getType() && $today <= $dateTimerPeriod['closingAt'];
        }

        $users = $bikeRide->getUsers();
        if (!$users->isEmpty() && !$users->contains($user)) {
            return false;
        }

        return $bikeRideType->isRequireAvailability() && $dateTimerPeriod['displayAt'] < $today && $today <= $dateTimerPeriod['closingAt'];
    }
}
