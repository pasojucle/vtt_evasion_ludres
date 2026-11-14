<?php

declare(strict_types=1);

namespace App\UseCase\v2\SecondHandCategory;

use App\Entity\SecondHandCategory;
use App\Repository\Interface\SecondHandCategoryRepositoryInterface;
use App\Service\SoftDeleteService;

class RestoreSecondHandCategory
{
    public function __construct(
        private SecondHandCategoryRepositoryInterface $SecondHandCategoryRepository,
        private SoftDeleteService $softDeleteService,
    ) {
    }

    public function __invoke(SecondHandCategory $secondHandCategory): void
    {
        $this->softDeleteService->restore($secondHandCategory);

        $this->SecondHandCategoryRepository->save($secondHandCategory);
    }
}
