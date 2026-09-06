<?php

declare(strict_types=1);

namespace App\Dto\Enum;

enum ClusterResolverStatus: string
{
    case SUCCESS = 'success';
    case CAPACITY_EXCEEDED = 'exceeded';
    case NO_CLUSTER_AVAILABLE = 'no_cluster';
}