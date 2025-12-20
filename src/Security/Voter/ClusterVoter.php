<?php

namespace App\Security\Voter;

use DateTime;
use App\Entity\User;
use App\Entity\Cluster;
use App\Entity\Session;
use App\Entity\Enum\PermissionEnum;
use App\Repository\SessionRepository;
use App\Dto\DtoTransformer\UserDtoTransformer;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

class ClusterVoter extends Voter
{
    public const LIST = 'CLUSTER_LIST';
    public const ADD = 'CLUSTER_ADD';
    public const EDIT = 'CLUSTER_EDIT';

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
        return $attribute === self::EDIT && $subject instanceof Cluster;
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
            self::EDIT, self::ADD => $this->canEdit($token, $user, $subject, $isActiveUser, $isUserWithPermission),
            self::LIST => $this->canList($token, $isActiveUser, $isUserWithPermission),
            default => false
        };
    }

    private function canEdit(TokenInterface $token, User $user, ?Cluster $subject, bool $isActiveUser, bool $isUserWithPermission): bool
    {
        if ($this->accessDecisionManager->decide($token, ['ROLE_ADMIN'])) {
            return true;
        }

        $today = new DateTime();

        $startAt = $subject->getBikeRide()->getStartAt();
        $endAt = $subject->getBikeRide()->getEndAt();
        if (!$endAt) {
            $endAt = $startAt;
        }

        if ($today < $startAt->setTime(0, 0, 0) || $endAt->setTime(23, 59, 59) < $today) {
            return false;
        }

        return $isUserWithPermission && $isActiveUser && $this->getSession($subject, $user);
    }

    private function canList(TokenInterface $token, bool $isActiveUser, bool $isUserWithPermission): bool
    {
        if ($this->accessDecisionManager->decide($token, ['ROLE_ADMIN'])) {
            return true;
        }
        
        if (1 === preg_match('#^admin#', $this->requestStack->getCurrentRequest()->attributes->get('_route'))) {
            return $isUserWithPermission;
        }

        return false;
    }

    private function getSession(?Cluster$subject, User $user): ?Session
    {
        return $this->sessionRepository->findOneByUserAndCluster($user, $subject);
    }
}
