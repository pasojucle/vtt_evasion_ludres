<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\BikeRide;
use App\Entity\Level;
use App\Entity\Session;
use App\Form\Admin\SessionType;
use App\Form\SessionSwitchType;
use App\Service\SeasonService;
use App\Service\SessionService;
use App\ViewModel\BikeRidePresenter;
use App\ViewModel\UserPresenter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SessionController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SessionService $sessionService
    ) {
    }

    #[Route('/admin/seance/{session}', name: 'admin_session_present', methods: ['GET'])]
    public function adminPresent(
        Session $session,
        BikeRidePresenter $bikeRidePresenter
    ): Response {
        $isPresent = !$session->isPresent();

        $session->setIsPresent($isPresent);
        $this->entityManager->flush();

        $bikeRidePresenter->present($session->getCluster()->getBikeRide());

        return $this->render('cluster/show.html.twig', [
            'bikeRide' => $bikeRidePresenter->viewModel(),
            'bike_rides_filters' => [],
            'permission' => 7,
        ]);
    }

    #[Route('/admin/groupe/change/{session}', name: 'admin_bike_ride_switch_cluster', methods: ['GET', 'POST'])]
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
    public function adminSessionAdd(
        Request $request,
        BikeRidePresenter $bikeRidePresenter,
        SeasonService $seasonService,
        BikeRide $bikeRide
    ): Response {
        $clusters = $bikeRide->getClusters();
        $request->getSession()->set('admin_session_add_clusters', serialize($clusters));
        $form = $this->createForm(SessionType::class, ['season' => 'SEASON_' . $seasonService->getCurrentSeason()], ['filters' => ['bikeRide' => $bikeRide->getId()]]);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $user = $data['user'];
            $userSession = new Session();
            
            $userCluster = $this->sessionService->getCluster($bikeRide, $user, $clusters);
            $userSession->setUser($user)
                ->setCluster($userCluster);
            if ($user->getLevel()->getType() === Level::TYPE_FRAME) {
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

        $bikeRidePresenter->present($bikeRide);
        return $this->render('session/admin/add.html.twig', [
            'form' => $form->createView(),
            'bikeRide' => $bikeRidePresenter->viewModel(),
        ]);
    }

    #[Route('/admin/rando/supprime/{session}', name: 'admin_session_delete', methods: ['GET'])]
    public function adminSessionDelete(
        Session $session,
        UserPresenter $userPresenter
    ) {
        $userPresenter->present($session->getUser());
        $bikeRide = $session->getCluster()->getBikeRide();

        $this->entityManager->remove($session);
        $this->entityManager->flush();

        $this->addFlash('success', $userPresenter->viewModel()->member->fullName . ' à bien été désincrit');

        return $this->redirectToRoute('admin_bike_ride_cluster_show', [
            'bikeRide' => $bikeRide->getId(),
        ]);
    }
}
