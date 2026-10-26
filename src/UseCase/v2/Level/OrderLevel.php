<?php

declare(strict_types=1);

namespace App\UseCase\v2\Level;

use App\Entity\Level;
use App\Repository\Interface\LevelRepositoryInterface;
use App\Service\OrderByService;

class OrderLevel
{
    public function __construct(
        private LevelRepositoryInterface $levelRepository,
        private OrderByService $orderByService
    ) {
    }

    public function __invoke(Level $level, int $newOrder): void
    {
        $type = $level->getType();
        $levels = $this->levelRepository->findByType($type);

        $this->orderByService->setNewOrders($level, $levels, $newOrder);
        
        $this->levelRepository->save($level);
    }
}
