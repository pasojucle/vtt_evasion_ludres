<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\SkillDto;
use App\Entity\Skill;
use App\Service\LevelService;

class SkillDtoTransformer implements DtoTransformerInterface
{
    public function __construct(
        private readonly LevelService $levelService,
    ) {
    }

    public function fromEntity($skill): SkillDto
    {
        $skillDto = new SkillDto();
        if ($skill) {
            $skillDto->id = $skill->getId();
            $skillDto->content = $skill->getContent();
            $skillDto->category = $this->getCategory($skill);
            $skillDto->level = $this->getLevel($skill);
        }

        return $skillDto;
    }

    public function fromEntities(iterable $skillEntities): array
    {
        $skills = [];
        foreach ($skillEntities as $skillEntity) {
            $skills[] = $this->fromEntity($skillEntity);
        }

        return $skills;
    }

    private function GetCategory(Skill $skill): array
    {
        return [
            'name' => $skill->getCategory()->getName(),
            'id' => $skill->getCategory()->getId(),
        ];
    }

    private function GetLevel(Skill $skill): array
    {
        return [
            'id' => $skill->getLevel()->getId(),
            'title' => $skill->getLevel()->getTitle(),
            'color' => $this->levelService->getColors($skill->getLevel()->getColor())
        ];
    }
}