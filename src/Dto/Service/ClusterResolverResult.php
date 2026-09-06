<?php

declare(strict_types=1);

namespace App\Dto\Service;

use App\Dto\Enum\ClusterResolverStatus;
use App\Entity\Cluster;

final readonly class ClusterResolverResult
{
    public function __construct(
        public bool $success,
        public ?Cluster $cluster = null,
        public ClusterResolverStatus $status = ClusterResolverStatus::SUCCESS,
    ) {
    }

    public static function success(Cluster $cluster): self
    {
        return new self(true, $cluster);
    }

    public static function failure(ClusterResolverStatus $status, ?Cluster $cluster = null): self
    {
        return new self(false, $cluster, $status);
    }
}
