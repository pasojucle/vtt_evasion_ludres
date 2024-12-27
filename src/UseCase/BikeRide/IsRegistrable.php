<?php

declare(strict_types=1);

namespace App\UseCase\BikeRide;

use App\Entity\BikeRide;
use App\Entity\Enum\RegistrationEnum;
use App\Entity\Identity;
use App\Entity\Level;
use App\Entity\User;
use App\Repository\IdentityRepository;
use DateInterval;
use DateTime;
use Symfony\Bundle\SecurityBundle\Security;

class IsRegistrable
{
    public function __construct(
        private readonly Security $security,
        private readonly IdentityRepository $identityRepository,
    ) {
    }
    
    public function execute(BikeRide $bikeRide, ?User $user): bool
    {
        if (!$user || !$this->security->isGranted('BIKE_RIDE_VIEW', $bikeRide) || RegistrationEnum::NONE === $bikeRide->getBikeRideType()->getRegistration()) {
            return false;
        }

        $users = $bikeRide->getUsers();
        if (!$users->isEmpty() && !$users->contains($user)) {
            return false;
        }
        
        $member = $this->identityRepository->findOneMemberByUser($user);
        if (!$member) {
            return false;
        }

        if ($bikeRide->getMinAge() && !$this->canParticipateByAge($bikeRide, $user, $member)) {
            return false;
        }

        if (!$bikeRide->registrationEnabled()) {
            return false;
        }

        $today = new DateTime();
        $intervalDisplay = new DateInterval('P' . $bikeRide->GetDisplayDuration() . 'D');
        $intervalClosing = new DateInterval('P' . $bikeRide->getClosingDuration() . 'D');

        $displayAt = $bikeRide->getStartAt()->setTime(0, 0, 0);
        $closingAt = $bikeRide->getStartAt()->setTime(23, 59, 59);

        return $displayAt->sub($intervalDisplay) <= $today && $today <= $closingAt->sub($intervalClosing);
    }

    private function canParticipateByAge(BikeRide $bikeRide, User $user, Identity $member): bool
    {
        if ($bikeRide->getBikeRideType()->isNeedFramers() && Level::TYPE_FRAME === $user->getLevel()->getType()) {
            return true;
        }

        $memberAge = (int) $bikeRide->getStartAt()->diff($member->getBirthDate())->format('%Y');
        if ($memberAge < $bikeRide->getMinAge() || ($bikeRide->getMaxAge() ?? 99) < $memberAge) {
            return false;
        }

        return true;
    }
}
