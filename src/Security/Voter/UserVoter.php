<?php

namespace App\Security\Voter;

use App\Dto\UserDto;
use App\Entity\User;
use App\Entity\Health;
use App\Entity\Licence;
use App\Entity\Identity;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

class UserVoter extends Voter
{
    public const NAV = 'USER_NAV';
    public const LIST = 'USER_LIST';
    public const EDIT = 'USER_EDIT';
    public const VIEW = 'USER_VIEW';

    public function __construct(
        private AccessDecisionManagerInterface $accessDecisionManager,
    )
    {
        
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (in_array($attribute, [self::NAV, self::LIST]) && !$subject) {
            return true;
        }

        return in_array($attribute, [self::EDIT, self::VIEW]) 
        && ($subject instanceof User || $subject instanceof UserDto || $subject instanceof Licence || $subject instanceof Identity || $subject instanceof Health || !$subject);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var User $user */
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        return match($attribute) {
            self::EDIT => $this->canEdit($token, $user, $subject),
            self::VIEW => $this->canView($token, $user, $subject),
            self::LIST => $this->canList($token, $user, $subject),
            self::NAV => $this->canNav($token, $user, $subject),
            default => false
        };
    }

    private function canEdit(TokenInterface $token, User $user, null|User|UserDto $subject): bool
    {
        if (!$this->accessDecisionManager->decide($token, ['ROLE_USER'])) {
            return false;
        }

        if ($this->accessDecisionManager->decide($token, ['ROLE_ADMIN'])) {
            return true;
        }

        if ($user->hasPermissions(User::PERMISSION_USER)) {
            return true;
        }

        return $this->isOwner($subject, $user);
    }

    private function canView(TokenInterface $token, User $user, null|User|UserDto $subject ): bool
    {
        if (!$subject || !$this->accessDecisionManager->decide($token, ['ROLE_USER']) ) {
            return false;
        }

        if ($this->canEdit($token, $user, $subject)) {
            return true;
        }

        return $this->accessDecisionManager->decide($token, ['ROLE_USER']) && $user->hasPermissions([User::PERMISSION_USER, User::PERMISSION_BIKE_RIDE]);
    }

    private function canList(TokenInterface $token, User $user, null|User|UserDto $subject): bool
    {
        if ($this->canEdit($token, $user, $subject)) {
            return true;
        }

        return $this->accessDecisionManager->decide($token, ['ROLE_USER']) && $user->hasPermissions([User::PERMISSION_USER, User::PERMISSION_BIKE_RIDE]);
    }

    private function canNav(TokenInterface $token, User $user, null|User|UserDto $subject): bool
    {
        if ($this->canEdit($token, $user, $subject)) {
            return true;
        }

        return $this->accessDecisionManager->decide($token, ['ROLE_USER']) && $user->hasPermissions(User::PERMISSION_USER);
    }


    private function isOwner(null|User|UserDto $subject, User $user): bool
    {
        if (!$subject) {
            return false;
        }

        return $subject === $user;
    }
}
