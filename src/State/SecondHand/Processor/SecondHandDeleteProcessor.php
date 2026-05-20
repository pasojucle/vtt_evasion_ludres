<?php

declare(strict_types=1);

namespace App\State\SecondHand\Processor;


use App\Entity\SecondHand;
use Doctrine\ORM\EntityManagerInterface;

class SecondHandDeleteProcessor
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    public function process(SecondHand $entity): void
    {
        $this->entityManager->remove($entity);

        $this->entityManager->flush();
    }
}