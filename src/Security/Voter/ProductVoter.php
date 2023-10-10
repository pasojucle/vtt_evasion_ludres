<?php

namespace App\Security\Voter;

use App\Entity\User;
use App\Entity\Product;
use App\Entity\OrderLine;
use App\Entity\OrderHeader;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

class ProductVoter extends Voter
{
    public const EDIT = 'PRODUCT_EDIT';
    public const VIEW = 'PRODUCT_VIEW';
    public const LIST = 'PRODUCT_LIST';

    public function __construct(
        private AccessDecisionManagerInterface $accessDecisionManager,
    )
    {
        
    }
    
    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::VIEW, self::LIST]) && ($subject instanceof Product || $subject instanceof OrderHeader || $subject instanceof OrderLine || !$subject);
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

    private function canEdit(TokenInterface $token, User $user, null|Product|OrderHeader|OrderLine $subject): bool
    {
        if (!$this->accessDecisionManager->decide($token, ['ROLE_USER'])) {
            return false;
        }
        
        if ($this->accessDecisionManager->decide($token, ['ROLE_ADMIN'])) {
            return true;
        }

        if($user->hasPermissions(User::PERMISSION_PRODUCT)) {
            return true;
        }
        
        return $this->isOwner($subject, $user);
    }

    private function canView(TokenInterface $token, User $user, null|Product|OrderHeader|OrderLine $subject): bool
    {
        if (!$subject) {
            return false;
        }
        
        if ($this->canEdit($token, $user, $subject)) {
            return true;
        }

        return $this->accessDecisionManager->decide($token, ['ROLE_USER']);
    }

    private function canList(TokenInterface $token, User $user, null|Product|OrderHeader|OrderLine $subject): bool
    {
        if ($this->canEdit($token, $user, $subject)) {
            return true;
        }

        return $this->accessDecisionManager->decide($token, ['ROLE_USER']) && $user->hasPermissions(User::PERMISSION_PRODUCT);
    }

    private function isOwner(Product|OrderHeader|OrderLine|null $subject, User $user): bool
    {
        if (!$subject || $subject instanceof Product) {
            return false;
        }

        $orderHeader = ($subject instanceof OrderHeader) ? $subject : $subject->getOrderHeader();

        return $orderHeader->getUser() === $user;
    }
}
