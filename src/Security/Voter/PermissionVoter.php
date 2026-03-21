<?php

namespace App\Security\Voter;

use App\Entity\Enum\PermissionEnum;
use App\Entity\Member;
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
        return in_array($attribute, [self::EDIT, self::VIEW]) && $subject instanceof Member;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        /** @var Member $member */
        $member = $token->getUser();
        if (!$member instanceof Member) {
            return false;
        }

        return match ($attribute) {
            self::EDIT => $this->canEdit($token, $member),
            self::VIEW => $this->canView($token, $member, $subject),
            default => false
        };
    }

    private function canEdit(TokenInterface $token, Member $member): bool
    {
        if ($this->accessDecisionManager->decide($token, ['ROLE_ADMIN'])) {
            return true;
        }

        return $this->accessDecisionManager->decide($token, ['ROLE_USER']) && $member->hasPermissions(PermissionEnum::PERMISSION);
    }

    private function canView(TokenInterface $token, Member $member, ?Member $subject): bool
    {
        if ($this->canEdit($token, $member)) {
            return true;
        }

        return $this->accessDecisionManager->decide($token, ['ROLE_USER']) && ($member->hasPermissions(PermissionEnum::BIKE_RIDE) || $member->hasPermissions(PermissionEnum::USER));
    }
}
