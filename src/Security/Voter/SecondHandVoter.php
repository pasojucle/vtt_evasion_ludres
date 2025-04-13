<?php

namespace App\Security\Voter;

use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Entity\Enum\PermissionEnum;
use App\Entity\SecondHand;
use App\Entity\User;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class SecondHandVoter extends Voter
{
    public const EDIT = 'SECOND_HAND_EDIT';
    public const ADD = 'SECOND_HAND_ADD';
    public const VIEW = 'SECOND_HAND_VIEW';
    public const LIST = 'SECOND_HAND_LIST';

    public function __construct(
        private readonly AccessDecisionManagerInterface $accessDecisionManager,
        private readonly UserDtoTransformer $userDtoTransformer,
        private RequestStack $requestStack,
    ) {
    }
    
    protected function supports(string $attribute, mixed $subject): bool
    {
        if (in_array($attribute, [self::LIST, self::ADD]) && !$subject) {
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

        $isGrantedUser = $this->accessDecisionManager->decide($token, ['ROLE_USER']);
        $userDto = $this->userDtoTransformer->fromEntity($user);
        $isActiveUser = $isGrantedUser && $userDto->lastLicence->isActive;
        $isUserWithPermission = $isActiveUser && $user->hasPermissions(PermissionEnum::SECOND_HAND);
        
        return match ($attribute) {
            self::EDIT => $this->canEdit($token, $user, $subject, $isActiveUser, $isUserWithPermission),
            self::VIEW, self::ADD => $this->canView($token, $user, $subject, $isActiveUser, $isUserWithPermission),
            self::LIST => $this->canList($token, $isActiveUser, $isUserWithPermission),
            default => false
        };
    }

    private function canEdit(TokenInterface $token, User $user, ?SecondHand $subject, bool $isActiveUser, bool $isUserWithPermission): bool
    {
        if ($this->accessDecisionManager->decide($token, ['ROLE_ADMIN']) || $isUserWithPermission) {
            return true;
        }

        return $this->isOwner($subject, $user) && $isActiveUser;
    }

    private function canView(TokenInterface $token, User $user, ?SecondHand $subject, bool $isActiveUser, bool $isUserWithPermission): bool
    {
        if ($this->canEdit($token, $user, $subject, $isActiveUser, $isUserWithPermission)) {
            return true;
        }

        return $isActiveUser;
    }

    private function canList(TokenInterface $token, bool $isActiveUser, bool $isUserWithPermission): bool
    {
        if ($this->accessDecisionManager->decide($token, ['ROLE_ADMIN'])) {
            return true;
        }
        
        if (1 === preg_match('#^admin#', $this->requestStack->getCurrentRequest()->attributes->get('_route'))) {
            return $isUserWithPermission;
        }

        return $isActiveUser;
    }

    private function isOwner(?SecondHand $subject, User $user): bool
    {
        if (!$subject) {
            return false;
        }

        return $subject->getUser() === $user;
    }
}
