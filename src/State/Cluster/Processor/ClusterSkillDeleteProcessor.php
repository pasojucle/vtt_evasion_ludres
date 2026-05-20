<?php

declare(strict_types=1);

namespace App\State\Cluster\Processor;

use App\Entity\Cluster;
use App\Entity\Skill;
use Doctrine\ORM\EntityManagerInterface;

class ClusterSkillDeleteProcessor
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function process(Cluster $cluster, Skill $skill): void
    {
        $$cluster->removeSkill($skill);
        $this->entityManager->flush();
    }
}