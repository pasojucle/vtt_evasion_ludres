<?php

namespace App\Security\Voter;

use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Dto\UserDto;
use App\Entity\Health;
use App\Entity\Identity;
use App\Entity\Licence;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserVoter extends Voter
{
    public const SHARE = 'USER_SHARE';
    public const LIST = 'USER_LIST';
    public const EDIT = 'USER_EDIT';
    public const VIEW = 'USER_VIEW';

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
        && ($subject instanceof User || $subject instanceof UserDto || $subject instanceof Licence || $subject instanceof Identity || $subject instanceof Health);
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
        $isUserWithSharePermission = $isActiveUser && $user->hasPermissions([User::PERMISSION_USER, User::PERMISSION_BIKE_RIDE]);
        $isUserWithPermission = $isActiveUser && $user->hasPermissions(User::PERMISSION_USER);

        return match ($attribute) {
            self::EDIT, self::VIEW => $this->canEdit($token, $user, $subject, $isActiveUser, $isUserWithPermission),
            self::LIST => $this->canList($token, $isUserWithPermission),
            self::SHARE => $this->canShare($token, $user, $subject, $isActiveUser, $isUserWithPermission, $isUserWithSharePermission),
            default => false
        };
    }

    private function canEdit(TokenInterface $token, User $user, null|User|UserDto|Licence $subject, bool $isActiveUser, bool $isUserWithPermission): bool
    {
        if ($this->accessDecisionManager->decide($token, ['ROLE_ADMIN']) || $isUserWithPermission) {
            return true;
        }

        return $this->isOwner($subject, $user) && $isActiveUser;
    }

    private function canList(TokenInterface $token, bool $isUserWithPermission): bool
    {
        if ($this->accessDecisionManager->decide($token, ['ROLE_ADMIN'])) {
            return true;
        }
        
        return $isUserWithPermission;
    }

    private function canShare(TokenInterface $token, User $user, null|User|UserDto|Licence $subject, bool $isActiveUser, bool $isUserWithPermission, bool $isUserWithSharePermission): bool
    {
        if ($this->canEdit($token, $user, $subject, $isActiveUser, $isUserWithPermission)) {
            return true;
        }

        return $isUserWithSharePermission;
    }

    private function isOwner(null|User|UserDto|Licence $subject, User $user): bool
    {
        if (!$subject) {
            return false;
        }

        return $subject === $user;
    }
}
