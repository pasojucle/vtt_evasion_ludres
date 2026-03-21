<?php

namespace App\Security\Voter;

use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Dto\UserDto;
use App\Entity\Agreement;
use App\Entity\Enum\PermissionEnum;
use App\Entity\Health;
use App\Entity\Identity;
use App\Entity\Licence;
use App\Entity\LicenceAgreement;
use App\Entity\Member;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class MemberVoter extends Voter
{
    public const SHARE = 'MEMBER_SHARE';
    public const LIST = 'MEMBER_LIST';
    public const EDIT = 'MEMBER_EDIT';
    public const VIEW = 'MEMBER_VIEW';

    public function __construct(
        private AccessDecisionManagerInterface $accessDecisionManager,
        private readonly UserDtoTransformer $userDtoTransformer,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        if ($attribute === self::SHARE) {
            return true;
        }

        if ($attribute === self::LIST && !$subject) {
            return true;
        }

        return in_array($attribute, [self::EDIT, self::VIEW])
        && ($subject instanceof Member || $subject instanceof UserDto || $subject instanceof Licence || $subject instanceof Identity || $subject instanceof Health || $subject instanceof LicenceAgreement);
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
        $isUserWithSharePermission = $isActiveUser && $member->hasPermissions([PermissionEnum::USER, PermissionEnum::BIKE_RIDE]);
        $isUserWithPermission = $isActiveUser && $member->hasPermissions(PermissionEnum::USER);

        return match ($attribute) {
            self::EDIT, self::VIEW => $this->canEdit($token, $member, $subject, $isActiveUser, $isUserWithPermission),
            self::LIST => $this->canList($token, $isUserWithPermission),
            self::SHARE => $this->canShare($token, $member, $subject, $isActiveUser, $isUserWithPermission, $isUserWithSharePermission),
            default => false
        };
    }

    private function canEdit(TokenInterface $token, Member $member, null|Member|UserDto|Licence|LicenceAgreement|Identity $subject, bool $isActiveUser, bool $isUserWithPermission): bool
    {
        if ($this->accessDecisionManager->decide($token, ['ROLE_ADMIN']) || $isUserWithPermission) {
            return true;
        }

        return $this->isOwner($subject, $member) && $isActiveUser;
    }

    private function canList(TokenInterface $token, bool $isUserWithPermission): bool
    {
        if ($this->accessDecisionManager->decide($token, ['ROLE_ADMIN'])) {
            return true;
        }
        
        return $isUserWithPermission;
    }

    private function canShare(TokenInterface $token, Member $member, null|Member|UserDto|Licence|Agreement $subject, bool $isActiveUser, bool $isUserWithPermission, bool $isUserWithSharePermission): bool
    {
        if ($this->canEdit($token, $member, $subject, $isActiveUser, $isUserWithPermission)) {
            return true;
        }

        return $isUserWithSharePermission;
    }

    private function isOwner(null|Member|UserDto|Licence|LicenceAgreement $subject, Member $member): bool
    {
        if (!$subject) {
            return false;
        }

        if ($subject instanceof Member) {
            return $subject === $member;
        }

        if ($subject instanceof Licence) {
            return $subject->getMember() === $member;
        }

        if ($subject instanceof LicenceAgreement) {
            return $subject->getLicence()->getMember() === $member;
        }
        
        return false;
    }
}
