<?php

declare(strict_types=1);

namespace App\UseCase\BikeRide;

use App\Entity\BikeRide;
use App\Entity\BikeRideType;
use App\Entity\Level;
use App\Entity\User;
use DateInterval;
use DateTime;
use Symfony\Bundle\SecurityBundle\Security;

class IsRegistrable
{
    public function __construct(
        private readonly Security $security
    ) {
    }
    
    public function execute(BikeRide $bikeRide, ?User $user): bool
    {
        if (!$user || !$this->security->isGranted('BIKE_RIDE_VIEW', $bikeRide) || BikeRideType::REGISTRATION_NONE === $bikeRide->getBikeRideType()->getRegistration()) {
            return false;
        }

        $users = $bikeRide->getUsers();
        if (!$users->isEmpty() && !$users->contains($user)) {
            return false;
        }
        
        if (!$user->getMemberIdentity()) {
            return false;
        }

        if ($bikeRide->getMinAge() && $bikeRide->getMaxAge() && !$this->canParticipateByAge($bikeRide, $user)) {
            return false;
        }

        $today = new DateTime();
        $intervalDisplay = new DateInterval('P' . $bikeRide->GetDisplayDuration() . 'D');
        $intervalClosing = new DateInterval('P' . $bikeRide->getClosingDuration() . 'D');

        $displayAt = $bikeRide->getStartAt()->setTime(0, 0, 0);
        $closingAt = $bikeRide->getStartAt()->setTime(23, 59, 59);

        return $displayAt->sub($intervalDisplay) <= $today && $today <= $closingAt->sub($intervalClosing);
    }

    private function canParticipateByAge(BikeRide $bikeRide, User $user): bool
    {
        if ($bikeRide->getBikeRideType()->isNeedFramers() && Level::TYPE_FRAME === $user->getLevel()->getType()) {
            return true;
        }

        $memberAge = (int) $bikeRide->getStartAt()->diff($user->getMemberIdentity()->getBirthDate())->format('%Y');
        if ($memberAge < $bikeRide->getMinAge() || $bikeRide->getMaxAge() < $memberAge) {
            return false;
        }

        return true;
    }
}
