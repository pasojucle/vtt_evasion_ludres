<?php

namespace App\Security\Voter;

use DateTime;
use App\Entity\User;
use App\Entity\Cluster;
use App\Entity\Session;
use App\Entity\Summary;
use App\Entity\BikeRide;
use App\Entity\Enum\PermissionEnum;
use App\Repository\SessionRepository;
use App\Dto\DtoTransformer\UserDtoTransformer;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

class BikeRideVoter extends Voter
{
    public const LIST = 'BIKE_RIDE_LIST';
    public const ADD = 'BIKE_RIDE_ADD';
    public const EDIT = 'BIKE_RIDE_EDIT';
    public const VIEW = 'BIKE_RIDE_VIEW';
    public const EXPORT = 'BIKE_RIDE_CLUSTER_EXPORT';

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
        return in_array($attribute, [self::EDIT, self::VIEW, self::EXPORT])
        && ($subject instanceof BikeRide || $subject instanceof Cluster || $subject instanceof Session || $subject instanceof Summary);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        /** @var User $user */
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        $isGrantedUser = $this->accessDecisionManager->decide($token, ['ROLE_USER']);
        $userDto = $this->userDtoTransformer->fromEntity($user);
        $isActiveUser = $isGrantedUser && $userDto->lastLicence->isActive;
        $isUserWithPermission = $isActiveUser && $user->hasPermissions(PermissionEnum::BIKE_RIDE);

        return match ($attribute) {
            self::EXPORT => $this->canExport($token, $user, $subject, $isActiveUser),
            self::EDIT, self::ADD => $this->canEdit($token, $user, $subject, $isActiveUser, $isUserWithPermission),
            self::VIEW => $this->canView($token, $user, $subject, $isActiveUser, $isUserWithPermission),
            self::LIST => $this->canList($token, $isActiveUser, $isUserWithPermission),
            default => false
        };
    }

    private function canExport(TokenInterface $token, User $user, null|BikeRide|Cluster|Session|Summary $subject, bool $isActiveUser): bool
    {
        if ($this->accessDecisionManager->decide($token, ['ROLE_ADMIN'])) {
            return true;
        }

        return $isActiveUser && $user->hasPermissions([PermissionEnum::BIKE_RIDE, PermissionEnum::BIKE_RIDE_CLUSTER]) && $this->isOwner($subject, $user);
    }

    private function canEdit(TokenInterface $token, User $user, null|BikeRide|Cluster|Session|Summary $subject, bool $isActiveUser, bool $isUserWithPermission): bool
    {
        if ($this->accessDecisionManager->decide($token, ['ROLE_ADMIN'])) {
            return true;
        }

        list($startAt, $endAt) = match (true) {
            $subject instanceof Cluster => [$subject->getBikeRide()->getStartAt(), $subject->getBikeRide()->getEndAt()],
            $subject instanceof Session => [$subject->getCluster()->getBikeRide()->getStartAt(), $subject->getCluster()->getBikeRide()->getEndAt()],
            default => [$subject->getStartAt(), $subject->getEndAt()],
        };

        if (!$endAt) {
            $endAt = $startAt;
        }

        $today = new DateTime();
        if ($today < $startAt->setTime(0, 0, 0) || $endAt->setTime(23, 59, 59) < $today) {
            return false;
        }

        if ($isUserWithPermission && $this->getSession($subject, $user)) {
            return true;
        };

        return $this->isOwner($subject, $user) && $isActiveUser;
    }

    private function canView(TokenInterface $token, User $user, null|BikeRide|Cluster|Session|Summary $subject, bool $isActiveUser, bool $isUserWithPermission): bool
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

    private function getSession(BikeRide|Cluster|Session|Summary $subject, User $user): ?Session
    {
        if ($subject instanceof Session) {
            return $subject;
        }

        $bikeRide = ($subject instanceof BikeRide) ? $subject : $subject->getBikeRide();
        return $this->sessionRepository->findOneByUserAndBikeRide($user, $bikeRide);
    }

    private function isOwner(BikeRide|Cluster|Session|null|Summary $subject, User $user): bool
    {
        if (!$subject) {
            return false;
        }
        if ($subject instanceof Session) {
            return $subject->getUser() === $user;
        }
        $session = match (true) {
            $subject instanceof Cluster => $this->sessionRepository->findOneByUserAndCluster($user, $subject),
            $subject instanceof BikeRide => $this->sessionRepository->findOneByUserAndBikeRide($user, $subject),
            default => null,
        };
        if ($session) {
            return $session->getUser() === $user;
        }

        return false;
    }
}
