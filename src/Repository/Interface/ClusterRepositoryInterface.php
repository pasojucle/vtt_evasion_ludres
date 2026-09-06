<?php

declare(strict_types=1);

namespace App\Repository\Interface;

use App\Entity\BikeRide;
use App\Entity\Cluster;

interface ClusterRepositoryInterface
{
    public function save(Cluster $cluster, bool $flush = true): void;

    public function remove(Cluster $cluster, bool $flush = true): void;

    /**
     * @return Cluster[]
     */
    public function findByBikeRide(BikeRide $bikeRide): array;
}