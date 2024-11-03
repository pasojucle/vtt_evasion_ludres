<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;


use App\Entity\Skill;
use App\Dto\UserSkillDto;
use App\Service\LevelService;
use App\Entity\Enum\EvaluationEnum;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserSkillDtoTransformer implements DtoTransformerInterface
{
    public function __construct(
        private readonly LevelService $levelService,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function fromEntity($userSkill): UserSkillDto
    {
        $userSkillDto = new UserSkillDto();
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
            'category' => $skill->getCategory()->getName(),
            'level' => $skill->getLevel()->getTitle(),
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