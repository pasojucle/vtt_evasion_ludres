<?php

declare(strict_types=1);

namespace App\UseCase\v2\Level;

use App\Entity\Level;
use App\Repository\Interface\LevelRepositoryInterface;
use App\Service\OrderByService;
use App\Service\SoftDeleteService;

class DeleteLevel
{
    public function __construct(
        private LevelRepositoryInterface $levelRepository,
        private SoftDeleteService $softDeleteService,
        private OrderByService $orderByService,
    ) {
    }

    public function __invoke(Level $level): void
    {
        $type = $level->getType();
        $levels = $this->levelRepository->findByType($type);

        $this->softDeleteService->softDelete($level);
        $this->orderByService->setNewOrders($level, $levels, count($levels));
        
        $this->levelRepository->save($level);
    }
}
