<?php

declare(strict_types=1);

namespace App\UseCase\BikeRide;

use App\Entity\BikeRide;
use App\Entity\User;
use DateInterval;
use DateTime;

class IsRegistrable
{
    public function execute(BikeRide $bikeRide, ?User $user): bool
    {
        if (!$user || !$bikeRide->getBikeRideType()->isRegistrable()) {
            return false;
        }

        $users = $bikeRide->getUsers();
        if (!$users->isEmpty() && !$users->contains($user)) {
            return false;
        }

        $levels = $bikeRide->getLevels();
        $levelTypes = $bikeRide->getLevelTypes();
        if (!$levels->isEmpty() && !$levels->contains($user->getLevel()) && !empty($levelTypes) && !in_array($user->getLevel()->getType(), $levelTypes)) {
            return false;
        }

    
        if ($bikeRide->getMinAge()) {
            $interval = new DateInterval('P' . $bikeRide->getMinAge() . 'Y');
            if ($user->getMemberIdentity() && (new DateTime())->sub($interval) < $user->getMemberIdentity()->getBirthDate()) {
                return false;
            }
        }

        $today = new DateTime();
        $intervalDisplay = new DateInterval('P' . $bikeRide->GetDisplayDuration() . 'D');
        $intervalClosing = new DateInterval('P' . $bikeRide->getClosingDuration() . 'D');

        $displayAt = $bikeRide->getStartAt()->setTime(0, 0, 0);
        $closingAt = $bikeRide->getStartAt()->setTime(23, 59, 59);

        return $displayAt->sub($intervalDisplay) <= $today && $today <= $closingAt->sub($intervalClosing);
    }
}