<?php

declare(strict_types=1);

namespace App\UseCase\BikeRide;

use App\Entity\BikeRide;
use App\Entity\Enum\RegistrationEnum;
use App\Entity\Identity;
use App\Entity\Level;
use App\Entity\Member;
use App\Entity\User;
use DateInterval;
use DateTime;
use Symfony\Bundle\SecurityBundle\Security;

class IsRegistrable
{
    public function __construct(
        private readonly Security $security,
    ) {
    }
    
    public function execute(BikeRide $bikeRide, ?User $member): bool
    {
        if ($bikeRide->getBikeRideType()->isPublic() && $bikeRide->registrationEnabled()) {
            return $this->isWithinDisplayPeriod($bikeRide);
        }
    
        if (!$member instanceof Member || !$this->security->isGranted('BIKE_RIDE_VIEW', $bikeRide) || RegistrationEnum::NONE === $bikeRide->getBikeRideType()->getRegistration()) {
            return false;
        }

        $members = $bikeRide->getMembers();
        if (!$members->isEmpty() && !$members->contains($member)) {
            return false;
        }
        
        $identity = $member->getIdentity();
        if (!$identity) {
            return false;
        }

        if ($bikeRide->getMinAge() && !$this->canParticipateByAge($bikeRide, $member, $identity)) {
            return false;
        }
        
        return $this->isWithinDisplayPeriod($bikeRide);
    }

    private function canParticipateByAge(BikeRide $bikeRide, Member $member, Identity $identity): bool
    {
        if ($bikeRide->getBikeRideType()->isNeedFramers() && Level::TYPE_FRAME === $member->getLevel()->getType()) {
            return true;
        }

        $memberAge = (int) $bikeRide->getStartAt()->diff($identity->getBirthDate())->format('%Y');
        if ($memberAge < $bikeRide->getMinAge() || ($bikeRide->getMaxAge() ?? 99) < $memberAge) {
            return false;
        }

        return true;
    }

    private function isWithinDisplayPeriod(BikeRide $bikeRide): bool
    {
        $today = new DateTime();
        $intervalDisplay = new DateInterval('P' . $bikeRide->GetDisplayDuration() . 'D');

        $displayAt = $bikeRide->getStartAt()->setTime(0, 0, 0);
        $closingAt = $bikeRide->getStartAt()->setTime(23, 59, 59);
 
        return $displayAt->sub($intervalDisplay) <= $today && $today <= $closingAt;
    }
}
