<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\SkillDto;
use App\Entity\Skill;
use App\Service\LevelService;

class SkillDtoTransformer
{
    public function __construct(
        private readonly LevelService $levelService,
    ) {
    }

    public function fromEntity(?Skill $skill): SkillDto
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

    public function fromEntities(array $skillEntities): array
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
            'title' => $skill->getLevel()->getTitle(),
            'color' => $this->levelService->getColors($skill->getLevel()->getColor())
        ];
    }
}