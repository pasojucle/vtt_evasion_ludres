<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Cluster;
use App\UseCase\Cluster\ExportCluster;
use App\ViewModel\BikeRidePresenter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ClusterController extends AbstractController
{
    #[Route('/admin/groupe/complete/{cluster}', name: 'admin_cluster_complete', options:['expose' => true], methods: ['GET'])]
    public function adminClusterComplete(
        EntityManagerInterface $entityManager,
        BikeRidePresenter $presenter,
        Cluster $cluster
    ): Response {
        $cluster->setIsComplete(!$cluster->isComplete());
        $entityManager->flush();

        $bikeRide = $cluster->getBikeRide();
        $presenter->present($bikeRide);

        return $this->render('cluster/show.html.twig', [
            'bikeRide' => $presenter->viewModel(),
            'bike_rides_filters' => [],
        ]);
    }

    #[Route('/admin/groupe/export/{cluster}', name: 'admin_cluster_export', methods: ['GET'])]
    public function adminClusterExport(
        ExportCluster $exportCluster,
        Cluster $cluster
    ): Response {
        return $exportCluster->execute($cluster);
    }
}
