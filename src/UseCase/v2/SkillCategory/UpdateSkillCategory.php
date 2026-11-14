<?php

declare(strict_types=1);

namespace App\UseCase\v2\SkillCategory;

use App\Entity\SkillCategory;
use App\Repository\Interface\SkillCategoryRepositoryInterface;

class UpdateSkillCategory
{
    public function __construct(
        private SkillCategoryRepositoryInterface $SkillCategoryRepository,
    ) {
    }

    public function __invoke(SkillCategory $skillCategory): void
    {
        $this->SkillCategoryRepository->save($skillCategory);
    }
}
