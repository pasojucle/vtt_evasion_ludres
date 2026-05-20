<?php

declare(strict_types=1);

namespace App\State\Level\Processor;

use App\Entity\Level;
use App\Repository\LevelRepository;
use App\Service\OrderByService;
use Doctrine\ORM\EntityManagerInterface;

class LevelDeleteProcessor
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LevelRepository $levelRepository,
        private OrderByService $orderByService,
    ) {}

    public function process(Level $entity): int
    {
        $type = $entity->getType();

        $entity->setIsDeleted(true);
        $this->entityManager->flush();

        $levels = $this->levelRepository->findByType($type);
        $this->orderByService->ResetOrders($levels);

        return $type;
    }
}