<?php

declare(strict_types=1);

namespace App\State\Link\Processor;

use App\Entity\Link;
use Doctrine\ORM\EntityManagerInterface;

class LinkDeleteProcessor
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function process(Link $entity): void
    {
        $this->entityManager->remove($entity);
        $this->entityManager->flush();
    }
}