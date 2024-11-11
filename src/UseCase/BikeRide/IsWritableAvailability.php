<?php

declare(strict_types=1);

namespace App\UseCase\BikeRide;

use App\Entity\BikeRide;
use App\Entity\Enum\RegistrationEnum;
use App\Entity\Level;
use App\Entity\User;
use DateTimeImmutable;
use Symfony\Bundle\SecurityBundle\Security;

class IsWritableAvailability
{
    public function __construct(
        private readonly Security $security
    ) {
    }
    
    public function execute(BikeRide $bikeRide, ?User $user): bool
    {
        $bikeRideType = $bikeRide->getBikeRideType();

        if (RegistrationEnum::NONE === $bikeRideType->getRegistration()) {
            return false;
        }

        $today = new DateTimeImmutable();
       
        return $this->security->isGranted('BIKE_RIDE_VIEW', $bikeRide) && Level::TYPE_FRAME === $user?->getLevel()?->getType() && $bikeRideType->isNeedFramers() && $today->setTime(0, 0, 0) <= $bikeRide->getStartAt()->setTime(23, 59, 59);
    }
}
