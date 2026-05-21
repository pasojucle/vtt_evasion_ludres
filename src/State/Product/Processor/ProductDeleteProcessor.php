<?php

declare(strict_types=1);

namespace App\State\Product\Processor;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;

class ProductDeleteProcessor
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function process(Product $entity): void
    {
        $entity->setDeleted(true);
        $this->entityManager->flush();
    }
}
