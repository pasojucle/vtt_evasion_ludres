<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Cluster;
use App\Entity\BikeRide;
use App\Form\Admin\ClusterType;
use App\UseCase\Cluster\ExportCluster;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Dto\DtoTransformer\ClusterDtoTransformer;
use App\Dto\DtoTransformer\BikeRideDtoTransformer;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class ClusterController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private BikeRideDtoTransformer $bikeRideDtoTransformer,
        private ClusterDtoTransformer $clusterDtoTransformer,
    ) {
    }

    #[Route('/admin/groupe/complete/{cluster}', name: 'admin_cluster_complete', options:['expose' => true], methods: ['GET'])]
    #[IsGranted('BIKE_RIDE_EDIT', 'cluster')]
    public function adminClusterComplete(
        Cluster $cluster
    ): Response {
        $cluster->setIsComplete(!$cluster->isComplete());
        $this->entityManager->flush();

        $html = $this->renderView('cluster/show.html.twig', [
            'bikeRide' => $this->bikeRideDtoTransformer->getHeaderFromEntity($cluster->getBikeRide()),
            'cluster' => $this->clusterDtoTransformer->fromEntity($cluster),
            'cluster_entity' => $cluster,
        ]);
        return new JsonResponse(['codeError' => 0, 'html' => $html]);
    }

    #[Route('/admin/groupe/ajoute/{bikeRide}', name: 'admin_cluster_add', methods: ['GET', 'POST'])]
    #[IsGranted('BIKE_RIDE_EDIT', 'bikeRide')]
    public function adminClusterAdd(
        Request $request,
        BikeRide $bikeRide
    ): Response {
        $form = $this->createForm(ClusterType::class);
        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $cluster = $form->getData();
            $cluster->setBikeRide($bikeRide);
            $this->entityManager->persist($cluster);
            $this->entityManager->flush();

            return $this->redirectToRoute('admin_bike_ride_cluster_show', ['bikeRide' => $bikeRide->getId()]);
        }

        return $this->render('cluster/edit.html.twig', [
            'bikeRide' => $this->bikeRideDtoTransformer->fromEntity($bikeRide),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/groupe/edit/{bikeRide}/{cluster}', name: 'admin_cluster_edit', methods: ['GET', 'POST'])]
    #[IsGranted('BIKE_RIDE_EDIT', 'bikeRide')]
    public function adminClusterEdit(
        Request $request,
        BikeRide $bikeRide,
        Cluster $cluster
    ): Response {
        $form = $this->createForm(ClusterType::class, $cluster);
        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('admin_bike_ride_cluster_show', ['bikeRide' => $bikeRide->getId()]);
        }

        return $this->render('cluster/edit.html.twig', [
            'bikeRide' => $this->bikeRideDtoTransformer->fromEntity($bikeRide),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/groupe/show/{cluster}', name: 'admin_cluster_show', methods: ['GET'], options:['expose' => true])]
    #[IsGranted('BIKE_RIDE_VIEW', 'cluster')]
    public function adminClusterShow(
        Cluster $cluster
    ): Response {
        $html = $this->renderView('cluster/show.html.twig', [
            'bikeRide' => $this->bikeRideDtoTransformer->getHeaderFromEntity($cluster->getBikeRide()),
            'cluster' => $this->clusterDtoTransformer->fromEntity($cluster),
            'cluster_entity' => $cluster,
        ]);
        return new JsonResponse(['codeError' => 0, 'html' => $html]);
    }

    #[Route('/admin/groupe/export/{cluster}', name: 'admin_cluster_export', methods: ['GET'])]
    #[IsGranted('BIKE_RIDE_EDIT', 'cluster')]
    public function adminClusterExport(
        ExportCluster $exportCluster,
        Cluster $cluster
    ): Response {
        return $exportCluster->execute($cluster);
    }

    #[Route('/admin/groupe/supprime/{cluster}', name: 'admin_cluster_delete', methods: ['GET'])]
    #[IsGranted('BIKE_RIDE_EDIT', 'cluster')]
    public function adminClusterDelete(
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
