<?php

declare(strict_types=1);

namespace App\State\SkillCategory\Processor;

use App\Entity\SkillCategory;
use Doctrine\ORM\EntityManagerInterface;

class SkillCategoryDeleteProcessor
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function process(SkillCategory $entity): void
    {
        $this->entityManager->remove($entity);
        $this->entityManager->flush();
    }
}