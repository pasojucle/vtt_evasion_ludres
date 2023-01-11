<?php

declare(strict_types=1);

namespace App\ViewModel;

use App\Entity\Cluster;
use App\Entity\Level;
use App\Form\Admin\LevelType;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Tools\Pagination\Paginator;

class ClustersViewModel
{
    public ?array $clusters = [];

    public $hasClusters = false;

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
        $clustersView->hasClusters = !empty($clustersViewModel);

        return $clustersView;
    }

    public function hasFramerCluster(): bool
    {
        foreach ($this->clusters as $cluster) {
            if ('ROLE_FRAME' === $cluster->role) {
                return true;
            }
        }

        return false;
    }
}
