<?php

namespace App\Security\Voter;

use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Entity\BikeRide;
use App\Entity\Cluster;
use App\Entity\Session;
use App\Entity\User;
use App\Repository\SessionRepository;
use DateTime;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class BikeRideVoter extends Voter
{
    public const LIST = 'BIKE_RIDE_LIST';
    public const ADD = 'BIKE_RIDE_ADD';
    public const EDIT = 'BIKE_RIDE_EDIT';
    public const VIEW = 'BIKE_RIDE_VIEW';

    public function __construct(
        private readonly AccessDecisionManagerInterface $accessDecisionManager,
        private readonly SessionRepository $sessionRepository,
        private readonly RequestStack $requestStack,
        private readonly UserDtoTransformer $userDtoTransformer,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (in_array($attribute, [self::LIST, self::ADD]) && !$subject) {
            return true;
        }
        return in_array($attribute, [self::EDIT, self::VIEW])
        && ($subject instanceof BikeRide || $subject instanceof Cluster || $subject instanceof Session);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var User $user */
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        $isGrantedUser = $this->accessDecisionManager->decide($token, ['ROLE_USER']);
        $userDto = $this->userDtoTransformer->fromEntity($user);
        $isActiveUser = $isGrantedUser && $userDto->lastLicence->isActive;
        $isUserWithPermission = $isActiveUser && $user->hasPermissions(User::PERMISSION_BIKE_RIDE);

        return match ($attribute) {
            self::EDIT, self::ADD => $this->canEdit($token, $user, $subject, $isActiveUser, $isUserWithPermission),
            self::VIEW => $this->canView($token, $user, $subject, $isActiveUser, $isUserWithPermission),
            self::LIST => $this->canList($token, $isActiveUser, $isUserWithPermission),
            default => false
        };
    }

    private function canEdit(TokenInterface $token, User $user, BikeRide|Cluster|Session $subject, bool $isActiveUser, bool $isUserWithPermission): bool
    {
        if ($this->accessDecisionManager->decide($token, ['ROLE_ADMIN'])) {
            return true;
        }

        $startAt = match (true) {
            $subject instanceof Cluster => $subject->getBikeRide()->getStartAt(),
            $subject instanceof Session => $subject->getCluster()->getBikeRide()->getStartAt(),
            default => $subject->getStartAt(),
        };

        $today = new DateTime();
        if ($today < $startAt->setTime(0, 0, 0) || $startAt->setTime(23, 59, 59) < $today) {
            return false;
        }

        if ($isUserWithPermission && $this->getSession($subject, $user)) {
            return true;
        };

        return $this->isOwner($subject, $user) && $isActiveUser;
    }

    private function canView(TokenInterface $token, User $user, null|BikeRide|Cluster|Session $subject, bool $isActiveUser, bool $isUserWithPermission): bool
    {
        if ($this->canEdit($token, $user, $subject, $isActiveUser, $isUserWithPermission)) {
            return true;
        }

        return $isActiveUser;
    }

    private function canList(TokenInterface $token, bool $isActiveUser, bool $isUserWithPermission): bool
    {
        if ($this->accessDecisionManager->decide($token, ['ROLE_ADMIN'])) {
            return true;
        }
        
        if (1 === preg_match('#^admin#', $this->requestStack->getCurrentRequest()->attributes->get('_route'))) {
            return $isUserWithPermission;
        }

        return $isActiveUser;
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
