<?php

declare(strict_types=1);

namespace App\UseCase\v2\SkillCategory;

use App\Entity\SkillCategory;
use App\Repository\Interface\SkillCategoryRepositoryInterface;
use App\Service\SoftDeleteService;

class DeleteSkillCategory
{
    public function __construct(
        private SkillCategoryRepositoryInterface $SkillCategoryRepository,
        private SoftDeleteService $softDeleteService,
    ) {
    }

    public function __invoke(SkillCategory $skillCategory): void
    {
        $this->softDeleteService->softDelete($skillCategory);

        $this->SkillCategoryRepository->save($skillCategory);
    }
}
