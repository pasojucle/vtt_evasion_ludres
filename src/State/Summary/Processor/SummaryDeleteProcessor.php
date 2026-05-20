<?php

declare(strict_types=1);

namespace App\State\Summary\Processor;

use App\Entity\Summary;
use Doctrine\ORM\EntityManagerInterface;

class SummaryDeleteProcessor
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function process(Summary $entity): void
    {
        $this->entityManager->remove($entity);
        $this->entityManager->flush();
    }
}