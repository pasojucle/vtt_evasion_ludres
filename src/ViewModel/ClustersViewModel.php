<?php

declare(strict_types=1);

namespace App\ViewModel;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Tools\Pagination\Paginator;

class ClustersViewModel
{
    public ?array $clusters = [];

    public static function fromClusters(array|Paginator|Collection $clusters, ServicesPresenter $services): ClustersViewModel
    {
        $clustersViewModel = [];
        if (!empty($clusters)) {
            foreach ($clusters as $cluster) {
                $clustersViewModel[] = ClusterViewModel::fromCluster($cluster, $services);
            }
        }

        $clustersView = new self();
        $clustersView->clusters = $clustersViewModel;

        return $clustersView;
    }
}
