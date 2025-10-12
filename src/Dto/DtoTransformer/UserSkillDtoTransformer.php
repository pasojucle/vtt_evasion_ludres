<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\UserSkillDto;
use App\Entity\Enum\EvaluationEnum;
use App\Entity\Skill;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserSkillDtoTransformer implements DtoTransformerInterface
{
    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function fromEntity($userSkill): UserSkillDto
    {
        $userSkillDto = new UserSkillDto();

        $skill = $userSkill-> getSkill();
        $userSkillDto->evaluation = ($userSkill->getEvaluation()) ? $this->getEvaluation($userSkill->getEvaluation()) : null;
        $userSkillDto->evaluateAt = ($userSkill->getEvaluateAt()) ? $userSkill->getEvaluateAt()->format('d/m/Y') : null;
        $userSkillDto->content = $skill->getContent();
        $userSkillDto->category = ['id' => $skill->getCategory()->getId()];
        $userSkillDto->level = ['id' => $skill->getLevel()->getId()];

        return $userSkillDto;
    }

    public function fromEntities(iterable $userSkillEntities): array
    {
        $userSkills = [];
        foreach ($userSkillEntities as $userSkillEntity) {
            $userSkills[] = $this->fromEntity($userSkillEntity);
        }

        return $userSkills;
    }

    private function getEvaluation(EvaluationEnum $eveluation): array
    {
        return [
            'value' => ucfirst($eveluation->trans($this->translator)),
            'color' => $eveluation->color(),
        ];
    }
}
