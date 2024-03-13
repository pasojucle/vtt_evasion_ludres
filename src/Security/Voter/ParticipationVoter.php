<?php

namespace App\Security\Voter;

use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ParticipationVoter extends Voter
{
    public const VIEW = 'PARTICIPATION_VIEW';

    public function __construct(
        private readonly AccessDecisionManagerInterface $accessDecisionManager,
        private readonly UserDtoTransformer $userDtoTransformer,
    ) {
    }
    
    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::VIEW && !$subject;
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
        $isUserWithPermission = $isActiveUser && $user->hasPermissions(User::PERMISSION_SECOND_HAND);
        
        return $this->canView($token, $isUserWithPermission);
    }
    
    private function canView(TokenInterface $token, bool $isUserWithPermission): bool
    {
        if ($this->accessDecisionManager->decide($token, ['ROLE_ADMIN'])) {
            return true;
        }

        return $isUserWithPermission;
    }
}
