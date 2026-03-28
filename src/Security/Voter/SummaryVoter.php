<?php

namespace App\Security\Voter;

use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Entity\BikeRide;
use App\Entity\Enum\PermissionEnum;
use App\Entity\Member;
use App\Entity\Session;
use App\Entity\Summary;
use App\Repository\SessionRepository;
use DateTime;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
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
        $isUserWithPermission = $isActiveUser && $member->hasPermissions(PermissionEnum::SUMMARY);

        return match ($attribute) {
            self::EDIT, self::ADD => $this->canEdit($token, $member, $subject, $isUserWithPermission),
            self::VIEW => $this->canView($token, $member, $subject, $isActiveUser, $isUserWithPermission),
            self::LIST => $this->canList($token, $isActiveUser, $isUserWithPermission),
            default => false
        };
    }

    private function canEdit(TokenInterface $token, Member $member, null|BikeRide|Summary $subject, bool $isUserWithPermission): bool
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

        return $isUserWithPermission && $this->getSession($subject, $member);
    }

    private function canView(TokenInterface $token, Member $member, null|Summary $subject, bool $isActiveUser, bool $isUserWithPermission): bool
    {
        if ($this->canEdit($token, $member, $subject, $isUserWithPermission)) {
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

    private function getSession(BikeRide|Summary $subject, Member $member): ?Session
    {
        $bikeRide = ($subject instanceof BikeRide) ? $subject : $subject->getBikeRide();
        return $this->sessionRepository->findOneByUserAndBikeRide($member, $bikeRide);
    }
}
