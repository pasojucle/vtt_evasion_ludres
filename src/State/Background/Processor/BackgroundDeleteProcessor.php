<?php

declare(strict_types=1);

namespace App\State\Background\Processor;


use App\Entity\Background;
use Doctrine\ORM\EntityManagerInterface;

class BackgroundDeleteProcessor
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    public function process(Background $entity): void
    {
        $this->entityManager->remove($entity);

        $this->entityManager->flush();
    }
}