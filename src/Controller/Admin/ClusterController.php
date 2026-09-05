<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\DtoTransformer\BikeRideDtoTransformer;
use App\Entity\BikeRide;
use App\Entity\Cluster;
use App\Form\Admin\ClusterType;
use App\Service\CacheService;
use App\Service\LogService;
use App\State\Cluster\Provider\ClusterReadProvider;
use App\State\Cluster\Provider\ClustersActivityReadProvider;
use App\UseCase\Cluster\ExportCluster;
use App\UseCase\Cluster\GetUsersOffSite;
use App\UseCase\Cluster\MailerSendUsersOffSite;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ClusterController extends AbstractCrudController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private BikeRideDtoTransformer $bikeRideDtoTransformer,
        private CacheService $cacheService,
    ) {
    }

    #[Route('/sortie/groupes/{bikeRide}', name: 'admin_cluster_list_activity', methods: ['GET'])]
    #[IsGranted('BIKE_RIDE_VIEW', 'bikeRide')]
    public function adminClustersBikeRide(
        Request $request,
        ClustersActivityReadProvider $provider,
        BikeRide $bikeRide,
    ): Response {
        return $this->handleComponentAction(
            $request,
            $provider,
            $bikeRide
        );
    }

    #[Route('/admin/groupe/complete/{cluster}', name: 'admin_cluster_complete', methods: ['GET', 'POST'])]
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

        return new JsonResponse(['codeError' => 0]);
    }

    #[Route('/admin/groupe/unlock/{cluster}', name: 'admin_cluster_unlock', methods: ['POST'])]
    #[IsGranted('BIKE_RIDE_EDIT', 'cluster')]
    public function adminClusterUnlock(
        Cluster $cluster
    ): Response {
        $cluster->setIsComplete(false);
        $this->entityManager->flush();
        $this->cacheService->deleteCacheIndex($cluster);

        return new JsonResponse(['codeError' => 0]);
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

            return $this->redirectToRoute('admin_cluster_list_activity', ['bikeRide' => $bikeRide->getId()]);
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

            return $this->redirectToRoute('admin_cluster_list_activity', ['bikeRide' => $bikeRide->getId()]);
        }

        return $this->render('cluster/edit.html.twig', [
            'bikeRide' => $this->bikeRideDtoTransformer->fromEntity($bikeRide),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/groupe/show/{cluster}', name: 'admin_cluster_show', methods: ['GET'])]
    #[IsGranted('BIKE_RIDE_VIEW', 'cluster')]
    public function adminClusterShow(
        Request $request,
        ClusterReadProvider $provider,
        Cluster $cluster
    ): Response {
        return $this->handleComponentAction(
            $request,
            $provider,
            $cluster
        );
    }

    #[Route('/admin/groupe/export/{cluster}', name: 'admin_cluster_export', methods: ['GET'])]
    #[IsGranted('BIKE_RIDE_CLUSTER_EXPORT', 'cluster')]
    public function adminClusterExport(
        LogService $logService,
        ExportCluster $exportCluster,
        Cluster $cluster
    ): Response {
        $logService->writeFromEntity($cluster);

        return $exportCluster->execute($cluster);
    }

    #[Route('/admin/groupe/supprime/{cluster}', name: 'admin_cluster_delete', methods: ['GET'])]
    #[IsGranted('BIKE_RIDE_EDIT', 'cluster')]
    public function adminClusterDelete(
        Cluster $cluster
    ): Response {
        $this->cacheService->deleteCacheIndex($cluster);
        $bikeRide = $cluster->getBikeRide();
        $this->entityManager->remove($cluster);
        $this->entityManager->flush();

        return $this->redirectToRoute('admin_cluster_list_activity', [
            'bikeRide' => $bikeRide->getId(),
        ]);
    }
}
