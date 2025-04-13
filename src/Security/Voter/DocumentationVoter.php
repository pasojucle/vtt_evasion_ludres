<?php

namespace App\Security\Voter;

use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Entity\Documentation;
use App\Entity\Enum\PermissionEnum;
use App\Entity\User;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class DocumentationVoter extends Voter
{
    public const EDIT = 'DOCUMENTATION_EDIT';
    public const ADD = 'DOCUMENTATION_ADD';
    public const VIEW = 'DOCUMENTATION_VIEW';
    public const LIST = 'DOCUMENTATION_LIST';

    public function __construct(
        private readonly AccessDecisionManagerInterface $accessDecisionManager,
        private readonly RequestStack $requestStack,
        private readonly UserDtoTransformer $userDtoTransformer,
    ) {
    }
    
    protected function supports(string $attribute, mixed $subject): bool
    {
        if (in_array($attribute, [self::LIST, self::ADD]) && !$subject) {
            return true;
        }
        return in_array($attribute, [self::EDIT, self::VIEW]) && ($subject instanceof Documentation || !$subject);
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
        $isUserWithPermission = $isActiveUser && $user->hasPermissions(PermissionEnum::DOCUMENTATION);

        return match ($attribute) {
            self::EDIT, self::ADD => $this->canEdit($token, $isUserWithPermission),
            self::VIEW => $this->canView($token, $isActiveUser, $isUserWithPermission),
            self::LIST => $this->canList($token, $isActiveUser, $isUserWithPermission),
            default => false
        };
    }
    private function canEdit(TokenInterface $token, bool $isUserWithPermission): bool
    {
        if ($this->accessDecisionManager->decide($token, ['ROLE_ADMIN'])) {
            return true;
        }

        return $isUserWithPermission;
    }

    private function canView(TokenInterface $token, bool $isActiveUser, bool $isUserWithPermission): bool
    {
        if ($this->canEdit($token, $isUserWithPermission)) {
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
}
