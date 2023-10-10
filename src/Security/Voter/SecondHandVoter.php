<?php

namespace App\Security\Voter;

use App\Entity\User;
use App\Entity\SecondHand;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

class SecondHandVoter extends Voter
{
    public const ADD = 'SECOND_HAND_ADD';
    public const EDIT = 'SECOND_HAND_EDIT';
    public const VIEW = 'SECOND_HAND_VIEW';
    public const LIST = 'SECOND_HAND_LIST';

    public function __construct(
        private AccessDecisionManagerInterface $accessDecisionManager,
    )
    {
        
    }
    
    protected function supports(string $attribute, mixed $subject): bool
    {
        if (in_array($attribute, [self::ADD, self::LIST]) && !$subject) {
            return true;
        }
        return in_array($attribute, [self::EDIT, self::VIEW]) && $subject instanceof SecondHand;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var User $user */
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }
        
        return match($attribute) {
            self::ADD => $this->canAdd($token, $user, $subject),
            self::EDIT => $this->canEdit($token, $user, $subject),
            self::VIEW => $this->canView($token, $user, $subject),
            self::LIST => $this->canList($token, $user, $subject),
            default => false
        };
    }

    private function canEdit(TokenInterface $token, User $user, null|SecondHand $subject): bool
    {
        if (!$this->accessDecisionManager->decide($token, ['ROLE_USER'])) {
            return false;
        }

        if ($this->accessDecisionManager->decide($token, ['ROLE_ADMIN'])) {
            return true;
        }

        if($user->hasPermissions(User::PERMISSION_SECOND_HAND)) {
            return true;
        }
        return $this->isOwner($subject, $user);
    }

    private function canView(TokenInterface $token, User $user, null|SecondHand $subject): bool
    {
        if (!$subject) {
            return false;
        }
        
        if ($this->canEdit($token, $user, $subject)) {
            return true;
        }

        return $this->accessDecisionManager->decide($token, ['ROLE_USER']);
    }

    private function canList(TokenInterface $token, User $user, null|SecondHand $subject): bool
    {
        if ($this->canEdit($token, $user, $subject)) {
            return true;
        }

        return $this->accessDecisionManager->decide($token, ['ROLE_USER']) && $user->hasPermissions(User::PERMISSION_SECOND_HAND);
    }

    private function canAdd(TokenInterface $token, User $user, null|SecondHand $subject): bool
    {
        if ($this->canEdit($token, $user, $subject)) {
            return true;
        }

        return $this->accessDecisionManager->decide($token, ['ROLE_USER']) && !$subject;
    }

    private function isOwner(?SecondHand $subject, User $user): bool
    {
        if (!$subject) {
            return false;
        }

        return $subject->getUser() === $user;
    }
}
