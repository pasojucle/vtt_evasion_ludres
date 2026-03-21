<?php

namespace App\Security\Voter;

use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Entity\BikeRide;
use App\Entity\Cluster;
use App\Entity\Enum\PermissionEnum;
use App\Entity\Session;
use App\Entity\Summary;
use App\Entity\Member;
use App\Repository\SessionRepository;
use DateTime;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

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
        /** @var Member $member */
        $member = $token->getUser();
        if (!$member instanceof Member) {
            return false;
        }

        $isGrantedUser = $this->accessDecisionManager->decide($token, ['ROLE_USER']);
        $userDto = $this->userDtoTransformer->fromEntity($member);
        $isActiveUser = $isGrantedUser && $userDto->lastLicence->isActive;
        $isUserWithPermission = $isActiveUser && $member->hasPermissions(PermissionEnum::BIKE_RIDE);

        return match ($attribute) {
            self::EXPORT => $this->canExport($token, $member, $subject, $isActiveUser),
            self::EDIT, self::ADD => $this->canEdit($token, $member, $subject, $isActiveUser, $isUserWithPermission),
            self::VIEW => $this->canView($token, $member, $subject, $isActiveUser, $isUserWithPermission),
            self::LIST => $this->canList($token, $isActiveUser, $isUserWithPermission),
            default => false
        };
    }

    private function canExport(TokenInterface $token, Member $member, null|BikeRide|Cluster|Session|Summary $subject, bool $isActiveUser): bool
    {
        if ($this->accessDecisionManager->decide($token, ['ROLE_ADMIN'])) {
            return true;
        }

        return $isActiveUser && $member->hasPermissions([PermissionEnum::BIKE_RIDE, PermissionEnum::BIKE_RIDE_CLUSTER]) && $this->isOwner($subject, $member);
    }

    private function canEdit(TokenInterface $token, Member $member, null|BikeRide|Cluster|Session|Summary $subject, bool $isActiveUser, bool $isUserWithPermission): bool
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

        if ($isUserWithPermission && $this->getSession($subject, $member)) {
            return true;
        };

        return $this->isOwner($subject, $member) && $isActiveUser;
    }

    private function canView(TokenInterface $token, Member $member, null|BikeRide|Cluster|Session|Summary $subject, bool $isActiveUser, bool $isUserWithPermission): bool
    {
        if ($this->canEdit($token, $member, $subject, $isActiveUser, $isUserWithPermission)) {
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

    private function getSession(BikeRide|Cluster|Session|Summary $subject, Member $member): ?Session
    {
        if ($subject instanceof Session) {
            return $subject;
        }

        $bikeRide = ($subject instanceof BikeRide) ? $subject : $subject->getBikeRide();
        return $this->sessionRepository->findOneByUserAndBikeRide($member, $bikeRide);
    }

    private function isOwner(BikeRide|Cluster|Session|null|Summary $subject, Member $member): bool
    {
        if (!$subject) {
            return false;
        }
        if ($subject instanceof Session) {
            return $subject->getMember() === $member;
        }
        $session = match (true) {
            $subject instanceof Cluster => $this->sessionRepository->findOneByUserAndCluster($member, $subject),
            $subject instanceof BikeRide => $this->sessionRepository->findOneByUserAndBikeRide($member, $subject),
            default => null,
        };
        if ($session) {
            return $session->getMember() === $member;
        }

        return false;
    }
}
