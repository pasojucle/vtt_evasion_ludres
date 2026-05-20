<?php

declare(strict_types=1);

namespace App\State\SlideshowDirectory\Processor;

use App\Entity\SlideshowDirectory;
use Doctrine\ORM\EntityManagerInterface;

class SlideshowDirectoryDeleteProcessor
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function process(SlideshowDirectory $entity): void
    {
        $this->entityManager->remove($entity);
        $this->entityManager->flush();
    }
}