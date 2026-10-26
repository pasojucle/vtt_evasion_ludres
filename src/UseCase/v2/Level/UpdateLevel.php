<?php

declare(strict_types=1);

namespace App\UseCase\v2\Level;

use App\Entity\Level;
use App\Repository\Interface\LevelRepositoryInterface;

class UpdateLevel
{
    public function __construct(
        private LevelRepositoryInterface $levelRepository,
    ) {
    }

    public function __invoke(Level $level): void
    {
        if (null === $level->getOrderBy() && null !== $level->getType()) {
            $order = $this->levelRepository->findNexOrderByType($level->getType());
            $level->setOrderBy($order);
        }
        
        $this->levelRepository->save($level);
    }
}
