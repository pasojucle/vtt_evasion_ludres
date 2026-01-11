<?php

namespace App\Security\Voter;

use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class WikiVoter extends Voter
{
    public const VIEW = 'WIKI_VIEW';

    public function __construct(
        private readonly AccessDecisionManagerInterface $accessDecisionManager,
        private readonly UserDtoTransformer $userDtoTransformer,
    ) {
    }
    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::VIEW && !$subject;
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

        $isUserWithPermission = $isActiveUser && !empty($user->getPermissions());
        if (self::VIEW === $attribute) {
            return $this->canView($token, $isUserWithPermission);
        }

        return false;
    }

    private function canView(TokenInterface $token, bool $isUserWithPermission): bool
    {
        if ($this->accessDecisionManager->decide($token, ['ROLE_ADMIN'])) {
            return true;
        }

        return $isUserWithPermission;
    }
}
