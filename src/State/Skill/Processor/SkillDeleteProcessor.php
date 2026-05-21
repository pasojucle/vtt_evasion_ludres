<?php

declare(strict_types=1);

namespace App\State\Skill\Processor;

use App\Entity\Skill;
use Doctrine\ORM\EntityManagerInterface;

class SkillDeleteProcessor
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function process(Skill $entity): int
    {
        $id = $entity->getId();
        $this->entityManager->remove($entity);
        $this->entityManager->flush();

        return $id;
    }
}