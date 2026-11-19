<?php

declare(strict_types=1);

namespace App\UseCase\v2\Background;

use App\Entity\Background;
use App\Repository\Interface\BackgroundRepositoryInterface;

class DeleteBackground
{
    public function __construct(
        private BackgroundRepositoryInterface $backgroundRepository,
    ) {
    }

    public function __invoke(Background $background): void
    {
        $this->backgroundRepository->remove($background);
    }
}
