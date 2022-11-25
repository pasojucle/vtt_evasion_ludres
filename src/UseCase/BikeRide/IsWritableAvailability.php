<?php

declare(strict_types=1);

namespace App\UseCase\BikeRide;

use App\Entity\BikeRide;
use App\Entity\Level;
use App\Entity\User;
use DateTimeImmutable;

class IsWritableAvailability
{
    public function execute(BikeRide $bikeRide, ?User $user): bool
    {
        $bikeRideType = $bikeRide->getBikeRideType();
        if (!$bikeRideType->isRegistrable()) {
            return false;
        }

        $level = (null !== $user) ? $user->getLevel() : null;
        $type = (null !== $level) ? $level->getType() : null;
        $today = new DateTimeImmutable();
        
        return Level::TYPE_FRAME === $type && $bikeRideType->isSchool() && $today->setTime(0, 0, 0) <= $bikeRide->getStartAt()->setTime(23, 59, 59);
    }
}
