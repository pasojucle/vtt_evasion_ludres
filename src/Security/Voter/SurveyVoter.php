<?php

namespace App\Security\Voter;

use App\Entity\User;
use App\Entity\Survey;
use App\Entity\Respondent;
use App\Entity\SurveyIssue;
use App\Entity\SurveyResponse;
use App\Entity\Enum\PermissionEnum;
use App\Dto\DtoTransformer\UserDtoTransformer;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

class SurveyVoter extends Voter
{
    public const EDIT = 'SURVEY_EDIT';
    public const ADD = 'SURVEY_ADD';
    public const VIEW = 'SURVEY_VIEW';
    public const LIST = 'SURVEY_LIST';

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

        return in_array($attribute, [self::EDIT, self::VIEW]) && ($subject instanceof Survey || $subject instanceof SurveyIssue || $subject instanceof SurveyResponse || $subject instanceof Respondent);
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
        $isUserWithPermission = $isActiveUser && $user->hasPermissions(PermissionEnum::SURVEY);

        return match ($attribute) {
            self::EDIT , self::ADD => $this->canEdit($token, $user, $subject, $isActiveUser, $isUserWithPermission),
            self::VIEW => $this->canView($token, $user, $subject, $isActiveUser, $isUserWithPermission),
            self::LIST => $this->canList($token, $isActiveUser, $isUserWithPermission),
            default => false
        };
    }

    private function canEdit(TokenInterface $token, User $user, null|Survey|SurveyIssue|SurveyResponse|Respondent $subject, bool $isActiveUser, bool $isUserWithPermission): bool
    {
        if ($this->accessDecisionManager->decide($token, ['ROLE_ADMIN']) || $isUserWithPermission) {
            return true;
        }

        return $this->isOwner($subject, $user) && $isActiveUser;
    }

    private function canView(TokenInterface $token, User $user, null|Survey|SurveyIssue|SurveyResponse|Respondent $subject, bool $isActiveUser, bool $isUserWithPermission): bool
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

    private function isOwner(null|Survey|SurveyIssue|SurveyResponse|Respondent $subject, User $user): bool
    {
        if (!$subject || $subject instanceof Survey || $subject instanceof SurveyIssue) {
            return false;
        }

        return $subject->getUser() === $user;
    }
}
