<?php

declare(strict_types=1);

namespace App\State\SlideshowImage\Processor;

use App\Entity\SlideshowImage;
use Doctrine\ORM\EntityManagerInterface;

class SlideshowImageDeleteProcessor
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function process(SlideshowImage $entity): void
    {
        $this->entityManager->remove($entity);
        $this->entityManager->flush();
    }
}