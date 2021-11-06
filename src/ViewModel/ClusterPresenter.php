<?php

namespace App\ViewModel;

use App\Entity\Cluster;
use App\Service\LicenceService;
use App\ViewModel\ClusterViewModel;


class ClusterPresenter extends AbstractPresenter
{
    public function present(?Cluster $cluster): void
    {
        if (null !== $cluster) {
            $this->viewModel = ClusterViewModel::fromCluster($cluster, $this->data);
        } else {
            $this->viewModel = new ClusterViewModel();
        }
    }


    public function viewModel(): ClusterViewModel
    {
        return $this->viewModel;
    }

}