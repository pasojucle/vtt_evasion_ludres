<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\SkillCategoryDto;
use App\Entity\SkillCategory;

class SkillCategoryDtoTransformer implements DtoTransformerInterface
{
    public function fromEntity($category): SkillCategoryDto
    {
        $categoryDto = new SkillCategoryDto();
        if ($category) {
            $categoryDto->id = $category->getId();
            $categoryDto->name = $category->getName();
        }

        return $categoryDto;
    }

    public function fromEntities(iterable $skillEntities): array
    {
        $skills = [];
        foreach ($skillEntities as $skillEntity) {
            $skills[] = $this->fromEntity($skillEntity);
        }

        return $skills;
    }
}
