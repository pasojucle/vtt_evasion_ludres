<?php

declare(strict_types=1);

namespace App\UseCase\v2\Cluster;

use App\Dto\Service\OperationResult;
use App\Entity\Cluster;
use App\Repository\Interface\ClusterRepositoryInterface;

class ToggleCompleteCluster
{
    public function __construct(
        private ClusterRepositoryInterface $clusterRepository,
    ) {
    }

    public function __invoke(
        Cluster $cluster,
        bool $isComplete,
    ): OperationResult {
        $cluster->setIsComplete($isComplete);
        $this->clusterRepository->save($cluster);

        return OperationResult::success();
    }
}
