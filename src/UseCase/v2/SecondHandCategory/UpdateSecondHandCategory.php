<?php

declare(strict_types=1);

namespace App\UseCase\v2\SecondHandCategory;

use App\Entity\SecondHandCategory;
use App\Repository\Interface\SecondHandCategoryRepositoryInterface;

class UpdateSecondHandCategory
{
    public function __construct(
        private SecondHandCategoryRepositoryInterface $SecondHandCategoryRepository,
    ) {
    }

    public function __invoke(SecondHandCategory $secondHandCategory): void
    {
        $this->SecondHandCategoryRepository->save($secondHandCategory);
    }
}
