<?php

declare(strict_types=1);

namespace App\Mapper\ClusterSkill;

use App\Dto\Enum\ColorVariant;
use App\Dto\View\ClusterSkill\ClusterSkillMembersView;
use App\Dto\View\ClusterSkill\ClusterSkillsSheetView;
use App\Dto\View\ClusterSkill\ClusterSkillView;
use App\Dto\View\EmptyView;
use App\Dto\View\LinkView;
use App\Entity\Cluster;
use App\Entity\Enum\EvaluationEnum;
use App\Entity\Member;
use App\Entity\MemberSkill;
use App\Entity\Skill;
use App\Service\CsrfTokenService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ClusterSkillsReadMapper
{
    public function __construct(
        private CsrfTokenService $csrfTokenService,
        private CsrfTokenManagerInterface $csrfTokenManager,
        private UrlGeneratorInterface $urlGenerator,
        private TranslatorInterface $translator,
    ) {
    }

    /**
     *@param Member[] $participants
     */
    public function mapToView(Cluster $cluster, array $participants): ClusterSkillsSheetView
    {
        return new ClusterSkillsSheetView(
            title: 'Compétences',
            description: sprintF('Compétences à évaluer au groupe %s', $cluster->getTitle()),
            action: 'Fermer',
            items: $cluster->getSkills()->map(fn (Skill $skill) =>
                new ClusterSkillView(
                    content: $skill->getContent(),
                    memberSkills: array_map(function (Member $particpant) use ($skill) {
                        $identity = $particpant->getIdentity();
                        $memberSkill = $particpant->getMemberSkills()->findFirst(
                            fn (int $key, MemberSkill $memberSkill) =>
                            $memberSkill->getSkill() === $skill
                        );
                        
                        return new ClusterSkillMembersView(
                            id: $particpant->getId(),
                            fullName: $identity->getFullName(),
                            unacquired: $this->evaluationButton($skill, $particpant, $memberSkill, EvaluationEnum::UNACQUIRED),
                            pending: $this->evaluationButton($skill, $particpant, $memberSkill, EvaluationEnum::PENDING),
                            acquired: $this->evaluationButton($skill, $particpant, $memberSkill, EvaluationEnum::ACQUIRED),
                        );
                    }, $participants)
                ))->toArray(),
            empty: new EmptyView(icon: 'lucide:badge-check', message: 'Aucune évaluation')
        );
    }

    private function evaluationButton(Skill $skill, Member $member, ?MemberSkill $memberSkill, EvaluationEnum $evaluation): LinkView
    {
        if (!$memberSkill) {
            $tokenId = $this->csrfTokenService->getTokenId($skill);
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
