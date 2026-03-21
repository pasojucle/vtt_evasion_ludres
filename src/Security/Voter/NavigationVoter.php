<?php

namespace App\Security\Voter;

use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Entity\Enum\PermissionEnum;
use App\Entity\Member;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class NavigationVoter extends Voter
{
    public const ADMIN = 'ADMIN_NAV';

    public function __construct(
        private readonly AccessDecisionManagerInterface $accessDecisionManager,
        private readonly UserDtoTransformer $userDtoTransformer,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::ADMIN;
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

        return match ($attribute) {
            self::ADMIN => $this->canDysplayAdminNav($token, $member, $isActiveUser),
            default => false
        };
    }


    private function canDysplayAdminNav(TokenInterface $token, Member $member, bool $isActiveUser): bool
    {
        if ($this->accessDecisionManager->decide($token, ['ROLE_ADMIN'])) {
            return true;
        }

        $hasAdminPermission = false;
        /** @var PermissionEnum $permission */
        foreach ($member->getPermissions() as $permission) {
            if ($permission->isAdmin()) {
                $hasAdminPermission = true;
            }
        }

        return $this->accessDecisionManager->decide($token, ['ROLE_USER']) && $isActiveUser && $hasAdminPermission;
    }
}
