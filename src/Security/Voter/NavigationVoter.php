<?php

namespace App\Security\Voter;

use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Entity\Enum\PermissionEnum;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
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
        dump($attribute);
        return $attribute === self::ADMIN;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        dump('NAVIGATION_ADMIN_NAV');
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
        dump($user->getPermissions());
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
