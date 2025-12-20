<?php

namespace App\Security\Voter;

use App\Entity\User;
use App\Entity\Enum\PermissionEnum;
use App\Dto\DtoTransformer\UserDtoTransformer;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

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
        /** @var User $user */
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        $isGrantedUser = $this->accessDecisionManager->decide($token, ['ROLE_USER']);
        $userDto = $this->userDtoTransformer->fromEntity($user);
        $isActiveUser = $isGrantedUser && $userDto->lastLicence->isActive;

        return match ($attribute) {
            self::ADMIN => $this->canDysplayAdminNav($token, $user, $isActiveUser),
            default => false
        };
    }


    private function canDysplayAdminNav(TokenInterface $token, User $user, bool $isActiveUser): bool
    {
        if ($this->accessDecisionManager->decide($token, ['ROLE_ADMIN'])) {
            return true;
        }

        $hasAdminPermission = false;
        /** @var PermissionEnum $permission */
        foreach ($user->getPermissions() as $permission) {
            if ($permission->isAdmin()) {
                $hasAdminPermission = true;
            }
        }

        return $this->accessDecisionManager->decide($token, ['ROLE_USER']) && $isActiveUser && $hasAdminPermission;
    }
}
