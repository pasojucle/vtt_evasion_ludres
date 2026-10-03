<?php

declare(strict_types=1);

namespace App\Mapper\ClusterSkill;

use App\Core\Dto\FlashMessage;
use App\Dto\Enum\ColorVariant;
use App\Dto\View\ClusterSkill\ClusterSkillMembersView;
use App\Dto\View\LinkView;
use App\Entity\Enum\EvaluationEnum;
use App\Entity\Member;
use App\Entity\MemberSkill;
use App\Entity\Skill;
use App\Service\CsrfTokenService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ClusterSkillMemberMapper
{
    public function __construct(
        private CsrfTokenService $csrfTokenService,
        private CsrfTokenManagerInterface $csrfTokenManager,
        private UrlGeneratorInterface $urlGenerator,
        private TranslatorInterface $translator,
    ) {
    }

    public function mapToView(
        Member $participant,
        Skill $skill,
        ?MemberSkill $memberSkill,
        ?FlashMessage $flashMessage = null
    ): ClusterSkillMembersView {
        $identity = $participant->getIdentity();
        
        return new ClusterSkillMembersView(
            id: sprintf('%s-%s', $participant->getId(), $skill->getId()),
            fullName: $identity->getFullName(),
            unacquired: $this->evaluationButton($skill, $participant, $memberSkill, EvaluationEnum::UNACQUIRED),
            pending: $this->evaluationButton($skill, $participant, $memberSkill, EvaluationEnum::PENDING),
            acquired: $this->evaluationButton($skill, $participant, $memberSkill, EvaluationEnum::ACQUIRED),
            flashMessage: $flashMessage,
        );
    }

    private function evaluationButton(Skill $skill, Member $member, ?MemberSkill $memberSkill, EvaluationEnum $evaluation): LinkView
    {
        if (!$memberSkill) {
            $tokenId = $this->csrfTokenService->getTokenId($member);
            $tokenValue = $this->csrfTokenManager->getToken($tokenId)->getValue();

            return new LinkView(
                url: $this->urlGenerator->generate('admin_cluster_member_skill_add', [
                'member' => $member->getId(),
                'skill' => $skill->getId(),
                'evaluation' => $evaluation->value,
                'csrfToken' => $tokenValue,
            ]),
                variant: ColorVariant::OUTLINE,
                label: ucfirst($evaluation->trans($this->translator)),
            );
        }

        $memberEvaluation = $memberSkill->getEvaluation();
        $tokenId = $this->csrfTokenService->getTokenId($memberSkill);
        $tokenValue = $this->csrfTokenManager->getToken($tokenId)->getValue();

        return new LinkView(
            url: $this->urlGenerator->generate('admin_cluster_member_skill_edit', [
            'memberSkill' => $memberSkill->getId(),
            'evaluation' => $evaluation->value,
            'csrfToken' => $tokenValue,
        ]),
            variant: $evaluation === $memberEvaluation
                ? $evaluation->variant()
                : ColorVariant::OUTLINE,
            label: ucfirst($evaluation->trans($this->translator)),
        );
    }
}
