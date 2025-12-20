<?php

namespace App\Security\Voter;

use App\Entity\User;
use App\Entity\Notification;
use App\Entity\Enum\PermissionEnum;
use App\Dto\DtoTransformer\UserDtoTransformer;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

class NotificationVoter extends Voter
{
    public const EDIT = 'MODAL_WINDOW_EDIT';
    public const ADD = 'MODAL_WINDOW_ADD';
    public const VIEW = 'MODAL_WINDOW_VIEW';
    public const LIST = 'MODAL_WINDOW_LIST';

    public function __construct(
        private AccessDecisionManagerInterface $accessDecisionManager,
        private readonly UserDtoTransformer $userDtoTransformer,
        private RequestStack $requestStack,
    ) {
    }
    
    protected function supports(string $attribute, mixed $subject): bool
    {
        if (in_array($attribute, [self::ADD, self::LIST]) && !$subject) {
            return true;
        }

        return in_array($attribute, [self::EDIT, self::VIEW]) && $subject instanceof Notification;
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
        $isUserWithPermission = $isActiveUser && $user->hasPermissions(PermissionEnum::MODAL_WINDOW);

        return match ($attribute) {
            self::EDIT, self::ADD => $this->canEdit($token, $subject, $isUserWithPermission),
            self::VIEW => $this->canView($token, $subject, $isActiveUser, $isUserWithPermission),
            self::LIST => $this->canList($token, $subject, $isActiveUser, $isUserWithPermission),
            default => false
        };
    }

    private function canEdit(TokenInterface $token, null|Notification $subject, bool $isUserWithPermission): bool
    {
        if ($this->accessDecisionManager->decide($token, ['ROLE_ADMIN'])) {
            return true;
        }

        return $subject && $isUserWithPermission;
    }

    private function canView(TokenInterface $token, null|Notification $subject, bool $isActiveUser, bool $isUserWithPermission): bool
    {
        if (!$subject) {
            return false;
        }
        
        if ($this->canEdit($token, $subject, $isUserWithPermission)) {
            return true;
        }

        return $isActiveUser;
    }

    private function canList(TokenInterface $token, null|Notification $subject, bool $isActiveUser, bool $isUserWithPermission): bool
    {
        if ($this->accessDecisionManager->decide($token, ['ROLE_ADMIN'])) {
            return true;
        }
        
        if (1 === preg_match('#^admin#', $this->requestStack->getCurrentRequest()->attributes->get('_route'))) {
            return $isUserWithPermission;
        }

        return $isActiveUser && !$subject;
    }
}
