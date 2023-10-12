<?php

namespace App\Security\Voter;

use App\Entity\BikeRide;
use App\Entity\Cluster;
use App\Entity\Session;
use App\Entity\User;
use App\Repository\SessionRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class BikeRideVoter extends Voter
{
    public const LIST = 'BIKE_RIDE_LIST';
    public const EDIT = 'BIKE_RIDE_EDIT';
    public const VIEW = 'BIKE_RIDE_VIEW';

    public function __construct(
        private AccessDecisionManagerInterface $accessDecisionManager,
        private SessionRepository $sessionRepository,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (self::LIST === $attribute && !$subject) {
            return true;
        }
        return in_array($attribute, [self::EDIT, self::VIEW])
        && ($subject instanceof BikeRide || $subject instanceof Cluster || $subject instanceof Session || !$subject);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var User $user */
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        return match ($attribute) {
            self::EDIT => $this->canEdit($token, $user, $subject),
            self::VIEW => $this->canView($token, $user, $subject),
            self::LIST => $this->canList($token, $user, $subject),
            default => false
        };
    }

    private function canEdit(TokenInterface $token, User $user, null|BikeRide|Cluster|Session $subject): bool
    {
        if (!$this->accessDecisionManager->decide($token, ['ROLE_USER'])) {
            return false;
        }

        if ($this->accessDecisionManager->decide($token, ['ROLE_ADMIN'])) {
            return true;
        }

        if ($user->hasPermissions(User::PERMISSION_BIKE_RIDE) && $this->getSession($subject, $user)) {
            return true;
        };

        return $this->isOwner($subject, $user);
    }

    private function canView(TokenInterface $token, User $user, null|BikeRide|Cluster|Session $subject): bool
    {
        if (!$subject || !$this->accessDecisionManager->decide($token, ['ROLE_USER'])) {
            return false;
        }

        if ($this->canEdit($token, $user, $subject)) {
            return true;
        }

        return $user->hasPermissions(User::PERMISSION_BIKE_RIDE);
    }

    private function canList(TokenInterface $token, User $user, null|BikeRide|Cluster|Session $subject): bool
    {
        if ($this->canView($token, $user, $subject)) {
            return true;
        }


        if ($this->accessDecisionManager->decide($token, ['ROLE_ADMIN'])) {
            return true;
        }

        return $this->accessDecisionManager->decide($token, ['ROLE_USER']) && $user->hasPermissions(User::PERMISSION_BIKE_RIDE);
    }

    private function getSession(BikeRide|Cluster|Session $subject, User $user): ?Session
    {
        if ($subject instanceof Session) {
            return $subject;
        }

        $bikeRide = ($subject instanceof BikeRide) ? $subject : $subject->getBikeRide();
        return $this->sessionRepository->findOneByUserAndBikeRide($user, $bikeRide);
    }

    private function isOwner(BikeRide|Cluster|Session|null $subject, User $user): bool
    {
        if (!$subject) {
            return false;
        }
        
        if ($subject instanceof Session) {
            return $subject->getUser() === $user;
        }

        $bikeRide = ($subject instanceof BikeRide) ? $subject : $subject->getBikeRide();
        $session = $this->sessionRepository->findOneByUserAndBikeRide($user, $bikeRide);
        if ($session) {
            return $session->getUser() === $user;
        }

        return false;
    }
}
