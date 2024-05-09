<?php

namespace App\Security\Voter;

use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Entity\BikeRide;
use App\Entity\Session;
use App\Entity\Summary;
use App\Entity\User;
use App\Repository\SessionRepository;
use DateTime;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class SummaryVoter extends Voter
{
    public const LIST = 'SUMMARY_LIST';
    public const ADD = 'SUMMARY_ADD';
    public const EDIT = 'SUMMARY_EDIT';
    public const VIEW = 'SUMMARY_VIEW';

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
        && ($subject instanceof BikeRide || $subject instanceof Summary);
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
        $isUserWithPermission = $isActiveUser && $user->hasPermissions(User::PERMISSION_SUMMARY);

        return match ($attribute) {
            self::EDIT, self::ADD => $this->canEdit($token, $user, $subject, $isUserWithPermission),
            self::VIEW => $this->canView($token, $user, $subject, $isActiveUser, $isUserWithPermission),
            self::LIST => $this->canList($token, $isActiveUser, $isUserWithPermission),
            default => false
        };
    }

    private function canEdit(TokenInterface $token, User $user, null|BikeRide|Summary $subject, bool $isUserWithPermission): bool
    {
        if ($this->accessDecisionManager->decide($token, ['ROLE_ADMIN'])) {
            return true;
        }

        list($startAt, $endAt) = ($subject instanceof Summary)
            ? [$subject->getBikeRide()->getStartAt(), $subject->getBikeRide()->getEndAt()]
            : [$subject->getStartAt(), $subject->getEndAt()];

        if (!$endAt) {
            $endAt = $startAt;
        }

        $today = new DateTime();
        if ($today < $startAt->setTime(0, 0, 0) || $endAt->setTime(23, 59, 59) < $today) {
            return false;
        }

        return $isUserWithPermission && $this->getSession($subject, $user);
    }

    private function canView(TokenInterface $token, User $user, null|Summary $subject, bool $isActiveUser, bool $isUserWithPermission): bool
    {
        if ($this->canEdit($token, $user, $subject, $isUserWithPermission)) {
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

    private function getSession(BikeRide|Summary $subject, User $user): ?Session
    {
        $bikeRide = ($subject instanceof BikeRide) ? $subject : $subject->getBikeRide();
        return $this->sessionRepository->findOneByUserAndBikeRide($user, $bikeRide);
    }
}
