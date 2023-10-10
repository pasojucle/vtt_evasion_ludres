<?php

namespace App\Security\Voter;

use App\Entity\User;
use App\Entity\ModalWindow;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

class ModalWindowVoter extends Voter
{
    public const EDIT = 'MODAL_WINDOW_EDIT';
    public const VIEW = 'MODAL_WINDOW_VIEW';
    public const LIST = 'MODAL_WINDOW_LIST';

    public function __construct(
        private AccessDecisionManagerInterface $accessDecisionManager,
    )
    {
        
    }
    
    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::VIEW, self::LIST]) && ($subject instanceof ModalWindow || !$subject);
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
            default => false
        };
    }

    private function canEdit(TokenInterface $token, User $user, null|ModalWindow $subject): bool
    {
        if (!$this->accessDecisionManager->decide($token, ['ROLE_USER'])) {
            return false;
        }
        
        if ($this->accessDecisionManager->decide($token, ['ROLE_ADMIN'])) {
            return true;
        }

        return $user->hasPermissions(User::PERMISSION_MODAL_WINDOW);
    }

    private function canView(TokenInterface $token, User $user, null|ModalWindow $subject): bool
    {
        if (!$subject || !$this->accessDecisionManager->decide($token, ['ROLE_USER'])) {
            return false;
        }
        
        if ($this->canEdit($token, $user, $subject)) {
            return true;
        }

        return $user->hasPermissions(User::PERMISSION_BIKE_RIDE);
    }

    private function canList(TokenInterface $token, User $user, null|ModalWindow $subject): bool
    {
        if ($this->canEdit($token, $user, $subject)) {
            return true;
        }

        return $this->accessDecisionManager->decide($token, ['ROLE_USER']) && $user->hasPermissions(User::PERMISSION_MODAL_WINDOW);
    }
}
