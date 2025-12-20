<?php

namespace App\Security\Voter;

use App\Entity\User;
use App\Entity\SlideshowImage;
use App\Entity\SlideshowDirectory;
use App\Entity\Enum\PermissionEnum;
use App\Dto\DtoTransformer\UserDtoTransformer;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

class SlideShowVoter extends Voter
{
    public const LIST = 'SLIDESHOW_LIST';
    public const EDIT = 'SLIDESHOW_EDIT';
    public const ADD = 'SLIDESHOW_ADD';
    public const VIEW = 'SLIDESHOW_VIEW';


    public function __construct(
        private AccessDecisionManagerInterface $accessDecisionManager,
        private readonly RequestStack $requestStack,
        private readonly UserDtoTransformer $userDtoTransformer,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (in_array($attribute, [self::LIST, self::ADD]) && !$subject) {
            return true;
        }
        return in_array($attribute, [self::EDIT, self::VIEW]) && ($subject instanceof SlideshowDirectory || $subject instanceof SlideshowImage);
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
        $isUserWithPermission = $isActiveUser && $user->hasPermissions(PermissionEnum::SLIDESHOW);

        return match ($attribute) {
            self::EDIT => $this->canEdit($token, $user, $subject, $isActiveUser, $isUserWithPermission),
            self::VIEW, self::ADD => $this->canView($token, $user, $subject, $isActiveUser, $isUserWithPermission),
            self::LIST => $this->canList($token, $isActiveUser, $isUserWithPermission),
            default => false
        };
    }


    private function canEdit(TokenInterface $token, User $user, SlideshowDirectory|SlideshowImage|null $subject, bool $isActiveUser, bool $isUserWithPermission): bool
    {
        return $this->accessDecisionManager->decide($token, ['ROLE_ADMIN']) || $isUserWithPermission;
    }

    private function canView(TokenInterface $token, User $user, SlideshowDirectory|SlideshowImage|null $subject, bool $isActiveUser, bool $isUserWithPermission): bool
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
}
