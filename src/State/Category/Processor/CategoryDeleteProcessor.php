<?php

declare(strict_types=1);

namespace App\State\Category\Processor;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;

class CategoryDeleteProcessor
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function process(Category $entity): void
    {
        $entity->setDeleted(true);
        $this->entityManager->flush();
    }
}