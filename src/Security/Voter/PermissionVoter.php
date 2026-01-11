<?php

namespace App\Security\Voter;

use App\Entity\Enum\PermissionEnum;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class PermissionVoter extends Voter
{
    public const EDIT = 'PERMISSION_EDIT';
    public const VIEW = 'PERMISSION_VIEW';

    public function __construct(
        private AccessDecisionManagerInterface $accessDecisionManager,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::VIEW]) && $subject instanceof User;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        /** @var User $user */
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        return match ($attribute) {
            self::EDIT => $this->canEdit($token, $user),
            self::VIEW => $this->canView($token, $user, $subject),
            default => false
        };
    }

    private function canEdit(TokenInterface $token, User $user): bool
    {
        if ($this->accessDecisionManager->decide($token, ['ROLE_ADMIN'])) {
            return true;
        }

        return $this->accessDecisionManager->decide($token, ['ROLE_USER']) && $user->hasPermissions(PermissionEnum::PERMISSION);
    }

    private function canView(TokenInterface $token, User $user, ?User $subject): bool
    {
        if ($this->canEdit($token, $user)) {
            return true;
        }

        return $this->accessDecisionManager->decide($token, ['ROLE_USER']) && ($user->hasPermissions(PermissionEnum::BIKE_RIDE) || $user->hasPermissions(PermissionEnum::USER));
    }
}
