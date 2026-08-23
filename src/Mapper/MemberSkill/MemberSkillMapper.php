<?php

declare(strict_types=1);

namespace App\Mapper\MemberSkill;

use App\Dto\Enum\ColorVariant;
use App\Dto\View\LinkView;
use App\Dto\View\MemberSkill\MemberSkillView;
use App\Entity\Enum\EvaluationEnum;
use App\Entity\MemberSkill;
use App\Service\CsrfTokenService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class MemberSkillMapper
{
    public function __construct(
        private TranslatorInterface $translator,
        private UrlGeneratorInterface $urlGenerator,
        private CsrfTokenService $csrfTokenService,
        private CsrfTokenManagerInterface $csrfTokenManager,
    ) {
    }
    public function mapToView(MemberSkill $memberSkill): MemberSkillView
    {
        $skill = $memberSkill->getSkill();
        $eveluation = $memberSkill->getEvaluation();
        $tokenId = $this->csrfTokenService->getTokenId($memberSkill);
        $tokenValue = $this->csrfTokenManager->getToken($tokenId)->getValue();

        return new MemberSkillView(
            id: $memberSkill->getId(),
            content: $skill->getContent(),
            unacquired: new LinkView(
                url: $this->urlGenerator->generate('admin_member_skill_edit', [
                    'memberSkill' => $memberSkill->getId(),
                    'evaluation' => EvaluationEnum::UNACQUIRED->value,
                    'csrfToken' => $tokenValue,
                ]),
                variant: EvaluationEnum::UNACQUIRED === $eveluation
                    ? EvaluationEnum::UNACQUIRED->variant()
                    : ColorVariant::OUTLINE,
                label: ucfirst(EvaluationEnum::UNACQUIRED->trans($this->translator)),
            ),
            pending: new LinkView(
                url: $this->urlGenerator->generate('admin_member_skill_edit', [
                    'memberSkill' => $memberSkill->getId(),
                    'evaluation' => EvaluationEnum::PENDING->value,
                    'csrfToken' => $tokenValue,
                ]),
                variant: EvaluationEnum::PENDING === $eveluation
                    ? EvaluationEnum::PENDING->variant()
                    : ColorVariant::OUTLINE,
                label: ucfirst(EvaluationEnum::PENDING->trans($this->translator)),
            ),
            acquired: new LinkView(
                url: $this->urlGenerator->generate('admin_member_skill_edit', [
                    'memberSkill' => $memberSkill->getId(),
                    'evaluation' => EvaluationEnum::ACQUIRED->value,
                    'csrfToken' => $tokenValue,
                ]),
                variant: EvaluationEnum::ACQUIRED === $eveluation
                    ? EvaluationEnum::ACQUIRED->variant()
                    : ColorVariant::OUTLINE,
                label: ucfirst(EvaluationEnum::ACQUIRED->trans($this->translator)),
            ),
        );
    }
}
