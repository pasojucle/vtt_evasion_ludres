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
        $userSkillDto->id = $userSkill->getId();
        $userSkillDto->skill = $this->getSkill($userSkill->getSkill());
        $userSkillDto->evaluation = ($userSkill->getEvaluation()) ? $this->getEvaluation($userSkill->getEvaluation()) : null;
        $userSkillDto->evaluateAt = ($userSkill->getEvaluateAt()) ? $userSkill->getEvaluateAt()->format('d/m/Y') : null;

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

    private function getSkill(Skill $skill): array
    {
        return [
            'content' => $skill->getContent(),
            'category' => [
                'id' => $skill->getCategory()->getId(),
                'name' => $skill->getCategory()->getName()
            ],
            'level' => [
                'id' => $skill->getLevel()->getId(),
                'title' => $skill->getLevel()->getTitle(),
            ],
        ];
    }

    private function getEvaluation(EvaluationEnum $eveluation): array
    {
        return [
            'value' => ucfirst($eveluation->trans($this->translator)),
            'color' => $eveluation->color(),
        ];
    }
}
