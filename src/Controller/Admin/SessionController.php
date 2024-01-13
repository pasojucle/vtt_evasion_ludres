<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\DtoTransformer\BikeRideDtoTransformer;
use App\Dto\DtoTransformer\ClusterDtoTransformer;
use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Entity\BikeRide;
use App\Entity\Level;
use App\Entity\Session;
use App\Form\Admin\SessionType;
use App\Form\SessionSwitchType;
use App\Repository\SessionRepository;
use App\Service\SeasonService;
use App\Service\SessionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class SessionController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SessionService $sessionService,
        private SessionRepository $sessionRepository,
        private BikeRideDtoTransformer $bikeRideDtoTransformer,
    ) {
    }

    #[Route('/admin/seance/{session}', name: 'admin_session_present', methods: ['GET'])]
    #[IsGranted('BIKE_RIDE_LIST')]
    public function adminPresent(
        Session $session,
        ClusterDtoTransformer $clusterDtoTransformer,
    ): Response {
        $isPresent = !$session->isPresent();

        $session->setIsPresent($isPresent);
        $this->entityManager->flush();
        $bikeRide = $session->getCluster()->getBikeRide();

        return $this->render('cluster/show.html.twig', [
            'bikeRide' => $this->bikeRideDtoTransformer->fromEntity($bikeRide),
            'clusters' => $clusterDtoTransformer->fromBikeRide($bikeRide),
            'bike_rides_filters' => [],
            'permission' => 7,
        ]);
    }

    #[Route('/admin/groupe/change/{session}', name: 'admin_bike_ride_switch_cluster', methods: ['GET', 'POST'])]
    #[IsGranted('BIKE_RIDE_LIST')]
    public function adminClusterSwitch(
        Request $request,
        Session $session
    ): Response {
        $bikeRide = $session->getCluster()->getBikeRide();
        $form = $this->createForm(SessionSwitchType::class, $session);

        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $session = $form->getData();
            $this->entityManager->flush();

            return $this->redirectToRoute('admin_bike_ride_cluster_show', [
                'bikeRide' => $bikeRide->getId(),
            ]);
        }

        return $this->render('session/switch.html.twig', [
            'bikeRide' => $bikeRide,
            'session' => $session,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/rando/inscription/{bikeRide}', name: 'admin_session_add', methods: ['GET', 'POST'])]
    #[IsGranted('BIKE_RIDE_EDIT', 'bikeRide')]
    public function adminSessionAdd(
        Request $request,
        SeasonService $seasonService,
        BikeRide $bikeRide
    ): Response {
        $clusters = $bikeRide->getClusters();
        $request->getSession()->set('admin_session_add_clusters', serialize($clusters));
        $form = $this->createForm(SessionType::class, ['season' => 'SEASON_' . $seasonService->getCurrentSeason()], [
            'filters' => ['bikeRide' => $bikeRide->getId(), 'is_final_licence' => false, ],
            'bikeRide' => $bikeRide,
        ]);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $user = $data['user'];

            if (null === $this->sessionRepository->findOneByUserAndBikeRide($user, $bikeRide)) {
                $userSession = new Session();
                $userCluster = $data['cluster'];
                if (null === $userCluster) {
                    $userCluster = $this->sessionService->getCluster($bikeRide, $user, $clusters);
                }
                $userSession->setUser($user)
                    ->setCluster($userCluster);
                if ($bikeRide->getBikeRideType()->isNeedFramers() && $user->getLevel()->getType() === Level::TYPE_FRAME) {
                    $userSession->setAvailability(Session::AVAILABILITY_REGISTERED);
                }
                $user->addSession($userSession);
                $this->entityManager->persist($userSession);

                $this->entityManager->flush();
                $this->addFlash('success', 'Le participant a bien été inscrit');

                $this->sessionService->checkEndTesting($user);

                return $this->redirectToRoute('admin_bike_ride_cluster_show', [
                    'bikeRide' => $bikeRide->getId(),
                ]);
            }
            $this->addFlash('danger', 'Le participant est déjà inscrit');
        }

        return $this->render('session/admin/add.html.twig', [
            'form' => $form->createView(),
            'bikeRide' => $this->bikeRideDtoTransformer->fromEntity($bikeRide),
        ]);
    }

    #[Route('/admin/rando/supprime/{session}', name: 'admin_session_delete', methods: ['GET'])]
    #[IsGranted('BIKE_RIDE_VIEW', 'session')]
    public function adminSessionDelete(
        Session $session,
        UserDtoTransformer $userDtoTransformer,
    ) {
        $userDto = $userDtoTransformer->fromEntity($session->getUser());
        $bikeRide = $session->getCluster()->getBikeRide();

        $this->entityManager->remove($session);
        $this->entityManager->flush();

        $this->addFlash('success', $userDto->member->fullName . ' à bien été désinscrit');

        return $this->redirectToRoute('admin_bike_ride_cluster_show', [
            'bikeRide' => $bikeRide->getId(),
        ]);
    }
}
