<?php

declare(strict_types=1);

namespace App\State\Activity\Processor;


use App\Entity\BikeRide;
use Doctrine\ORM\EntityManagerInterface;

class ActivityDeleteProcessor
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    public function process(BikeRide $entity): void
    {
        $entity->setDeleted(true);
        $this->entityManager->flush();
    }
}