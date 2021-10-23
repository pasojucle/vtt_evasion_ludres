<?php

namespace App\ViewModel;

use App\Entity\Cluster;
use App\Service\LicenceService;
use App\ViewModel\ClusterViewModel;


class ClusterPresenter 
{
    private LicenceService $licenceService;
    private $viewModel;

    public function __construct(LicenceService $licenceService)
    {
        $this->licenceService = $licenceService;
    }

    public function present(?Cluster $cluster): void
    {
        
        if (null !== $cluster) {
            $this->viewModel = ClusterViewModel::fromCluster($cluster, $this->licenceService);
        } else {
            $this->viewModel = new ClusterViewModel();
        }
    }


    public function viewModel(): ClusterViewModel
    {
        return $this->viewModel;
    }

}