<?php

declare(strict_types=1);

namespace App\UseCase\v2\Cluster;

use App\Entity\Cluster;
use App\Entity\Skill;
use App\Repository\Interface\ClusterRepositoryInterface;

class DeleteClusterSkill
{
    public function __construct(
        private ClusterRepositoryInterface $clusterRepository,
    ) {
    }

    public function __invoke(Cluster $cluster, Skill $skill): void
    {
        $cluster->removeSkill($skill);
        $this->clusterRepository->save($cluster);
    }
}
