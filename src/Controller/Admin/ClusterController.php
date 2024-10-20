<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\DtoTransformer\BikeRideDtoTransformer;
use App\Dto\DtoTransformer\ClusterDtoTransformer;
use App\Entity\BikeRide;
use App\Entity\Cluster;
use App\Form\Admin\ClusterType;
use App\Service\CacheService;
use App\UseCase\Cluster\ExportCluster;
use App\UseCase\Cluster\GetUsersOffSite;
use App\UseCase\Cluster\MailerSendUsersOffSite;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ClusterController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private BikeRideDtoTransformer $bikeRideDtoTransformer,
        private ClusterDtoTransformer $clusterDtoTransformer,
        private CacheService $cacheService,
    ) {
    }

    #[Route('/admin/groupe/complete/{cluster}', name: 'admin_cluster_complete', options:['expose' => true], methods: ['GET', 'POST'])]
    #[IsGranted('BIKE_RIDE_EDIT', 'cluster')]
    public function adminClusterComplete(
        Request $request,
        GetUsersOffSite $usersOffSite,
        MailerSendUsersOffSite $mailerSendUsersOffSite,
        Cluster $cluster
    ): Response {
        /** @var BikeRide $bikeRide */
        $bikeRide = $cluster->getBikeRide();
        list($usersOffSite, $response) = $usersOffSite->execute($request, $cluster);
        if ($response instanceof Response) {
            return $response;
        }

        $cluster->setIsComplete(!$cluster->isComplete());
        $this->entityManager->flush();
        $this->cacheService->deleteCacheIndex($cluster);
        $mailerSendUsersOffSite->execute($usersOffSite, $bikeRide);

        return new JsonResponse([
            'codeError' => 0,
            'html' => $this->renderView('cluster/show.html.twig', [
                'bikeRide' => $this->bikeRideDtoTransformer->getHeaderFromEntity($bikeRide),
                'cluster' => $this->clusterDtoTransformer->fromEntity($cluster),
                'cluster_entity' => $cluster,
            ]),
        ]);
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

            $this->cacheService->deleteCacheIndex($cluster);

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

            $this->cacheService->deleteCacheIndex($cluster);

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

        $this->cacheService->deleteCacheIndex($cluster);

        return $this->redirectToRoute('admin_bike_ride_cluster_show', [
            'bikeRide' => $bikeRide->getId(),
        ]);
    }

    #[Route('/admin/groupe/evaluations/{cluster}/{filtered}', name: 'admin_cluster_evaluations', defaults: ['filtered' => false], methods: ['GET', 'POST'])]
    #[IsGranted('BIKE_RIDE_EDIT', 'cluster')]
    public function adminClusterEvaluations(
        Request $request,
        Cluster $cluster,
        bool $filtered
    ): Response {

        return $this->render('cluster/admin/skill_list.html.twig', [
            'cluster' => $cluster,
        ]);
    }
}
