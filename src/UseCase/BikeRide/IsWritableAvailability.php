<?php

declare(strict_types=1);

namespace App\UseCase\BikeRide;

use App\Entity\BikeRide;
use App\Entity\BikeRideType;
use App\Entity\Level;
use App\Entity\User;
use DateTimeImmutable;

class IsWritableAvailability
{
    public function execute(BikeRide $bikeRide, ?User $user): bool
    {
        $bikeRideType = $bikeRide->getBikeRideType();
        ;
        if (BikeRideType::REGISTRATION_NONE === $bikeRideType->getRegistration()) {
            return false;
        }

        $level = (null !== $user) ? $user->getLevel() : null;
        $type = (null !== $level) ? $level->getType() : null;
        $today = new DateTimeImmutable();
        
        return Level::TYPE_FRAME === $type && BikeRideType::REGISTRATION_SCHOOL === $bikeRideType->getRegistration() && $today->setTime(0, 0, 0) <= $bikeRide->getStartAt()->setTime(23, 59, 59);
    }
}
