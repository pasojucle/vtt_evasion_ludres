<?php

declare(strict_types=1);

namespace App\UseCase\v2\Cluster;

use App\Dto\Service\OperationResult;
use App\Entity\Cluster;
use App\Entity\Skill;
use App\Repository\Interface\ClusterRepositoryInterface;

class AddClusterSkill
{
    public function __construct(
        private ClusterRepositoryInterface $clusterRepository,
    ) {
    }

    public function __invoke(
        Cluster $cluster,
        Skill $skill,
    ): OperationResult {
        $cluster->addSkill($skill);
        $this->clusterRepository->save($cluster);

        return OperationResult::success();
    }
}
