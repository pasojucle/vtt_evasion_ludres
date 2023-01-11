<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\BikeRide;
use App\Entity\Cluster;
use App\Form\Admin\ClusterType;
use App\UseCase\Cluster\ExportCluster;
use App\ViewModel\BikeRidePresenter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ClusterController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private BikeRidePresenter $presenter,
    ) {
    }
    #[Route('/admin/groupe/complete/{cluster}', name: 'admin_cluster_complete', options:['expose' => true], methods: ['GET'])]
    public function adminClusterComplete(
        Cluster $cluster
    ): Response {
        $cluster->setIsComplete(!$cluster->isComplete());
        $this->entityManager->flush();

        $bikeRide = $cluster->getBikeRide();
        $this->presenter->present($bikeRide);

        return $this->render('cluster/show.html.twig', [
            'bikeRide' => $this->presenter->viewModel(),
            'bike_rides_filters' => [],
        ]);
    }

    #[Route('/admin/groupe/edit/{bikeRide}/{cluster}', name: 'admin_cluster_edit', defaults:['cluster' => null], methods: ['GET', 'POST'])]
    public function adminClusterAdd(
        Request $request,
        BikeRide $bikeRide,
        ?Cluster $cluster
    ): Response {
        $form = $this->createForm(ClusterType::class, $cluster);
        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $cluster = $form->getData();
            $cluster->setBikeRide($bikeRide);
            $this->entityManager->persist($cluster);
            $this->entityManager->flush();

            return $this->redirectToRoute('admin_bike_ride_cluster_show', ['bikeRide' => $bikeRide->getId()]);
        }

        $this->presenter->present($bikeRide);
        return $this->render('cluster/add.html.twig', [
            'bikeRide' => $this->presenter->viewModel(),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/groupe/export/{cluster}', name: 'admin_cluster_export', methods: ['GET'])]
    public function adminClusterExport(
        ExportCluster $exportCluster,
        Cluster $cluster
    ): Response {
        return $exportCluster->execute($cluster);
    }

    #[Route('/admin/groupe/supprime/{cluster}', name: 'admin_cluster_delete', methods: ['GET'])]
    public function adminClusterDelete(
        ExportCluster $exportCluster,
        Cluster $cluster
    ): Response {
        $bikeRide = $cluster->getBikeRide();
        $this->entityManager->remove($cluster);
        $this->entityManager->flush();

        return $this->redirectToRoute('admin_bike_ride_cluster_show', [
            'bikeRide' => $bikeRide->getId(),
        ]);
    }
}
