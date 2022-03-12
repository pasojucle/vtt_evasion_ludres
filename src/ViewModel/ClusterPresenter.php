<?php

declare(strict_types=1);

namespace App\ViewModel;

use App\Entity\Cluster;

class ClusterPresenter extends AbstractPresenter
{
    private $viewModel;

    public function present(?Cluster $cluster): void
    {
        if (null !== $cluster) {
            $this->viewModel = ClusterViewModel::fromCluster($cluster, $this->services);
        } else {
            $this->viewModel = new ClusterViewModel();
        }
    }

    public function viewModel(): ClusterViewModel
    {
        return $this->viewModel;
    }
}
