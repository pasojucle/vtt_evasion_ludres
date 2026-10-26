<?php

declare(strict_types=1);

namespace App\UseCase\v2\Level;

use App\Entity\Level;
use App\Repository\Interface\LevelRepositoryInterface;
use App\Service\SoftDeleteService;

class RestoreLevel
{
    public function __construct(
        private LevelRepositoryInterface $levelRepository,
        private SoftDeleteService $softDeleteService,
    ) {
    }

    public function __invoke(Level $level): void
    {
        $this->softDeleteService->restore($level);
        
        $this->levelRepository->save($level);
    }
}
