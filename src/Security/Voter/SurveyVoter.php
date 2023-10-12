<?php

namespace App\Security\Voter;

use App\Entity\Respondent;
use App\Entity\Survey;
use App\Entity\SurveyIssue;
use App\Entity\SurveyResponse;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class SurveyVoter extends Voter
{
    public const EDIT = 'SURVEY_EDIT';
    public const VIEW = 'SURVEY_VIEW';
    public const LIST = 'SURVEY_LIST';

    public function __construct(
        private AccessDecisionManagerInterface $accessDecisionManager,
    ) {
    }
    
    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::VIEW, self::LIST]) && ($subject instanceof Survey || $subject instanceof SurveyIssue || $subject instanceof SurveyResponse || $subject instanceof Respondent || !$subject);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var User $user */
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        return match ($attribute) {
            self::EDIT => $this->canEdit($token, $user, $subject),
            self::VIEW => $this->canView($token, $user, $subject),
            self::LIST => $this->canList($token, $user, $subject),
            default => false
        };
    }

    private function canEdit(TokenInterface $token, User $user, null|Survey|SurveyIssue|SurveyResponse|Respondent $subject): bool
    {
        if (!$this->accessDecisionManager->decide($token, ['ROLE_USER'])) {
            return false;
        }

        if ($this->accessDecisionManager->decide($token, ['ROLE_ADMIN'])) {
            return true;
        }

        if ($user->hasPermissions(User::PERMISSION_SURVEY)) {
            return true;
        }
        return $this->isOwner($subject, $user);
    }

    private function canView(TokenInterface $token, User $user, null|Survey|SurveyIssue|SurveyResponse|Respondent $subject): bool
    {
        if (!$subject || !$this->accessDecisionManager->decide($token, ['ROLE_USER'])) {
            return false;
        }
        
        if ($this->canEdit($token, $user, $subject)) {
            return true;
        }

        return $user->hasPermissions(User::PERMISSION_SURVEY);
    }

    private function canList(TokenInterface $token, User $user, null|Survey|SurveyIssue|SurveyResponse|Respondent $subject): bool
    {
        if ($this->canEdit($token, $user, $subject)) {
            return true;
        }

        return $this->accessDecisionManager->decide($token, ['ROLE_USER']) && $user->hasPermissions(User::PERMISSION_SURVEY);
    }

    private function isOwner(null|Survey|SurveyIssue|SurveyResponse|Respondent $subject, User $user): bool
    {
        if (!$subject || $subject instanceof Survey || $subject instanceof SurveyIssue) {
            return false;
        }

        return $subject->getUser() === $user;
    }
}
