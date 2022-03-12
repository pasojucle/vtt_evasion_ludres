<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Session;
use App\Entity\BikeRide;
use App\Service\UserService;
use App\Form\SessionEditType;
use App\Service\SessionService;
use App\Service\BikeRideService;
use App\ViewModel\UserPresenter;
use App\Form\SessionAvailabilityType;
use App\Repository\SessionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SessionController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RequestStack $requestStack,
        private SessionService $sessionService, 
        private UserPresenter $userPresenter
    ) {
    }

    #[Route('/mon-compte/rando/inscription/{bikeRide}', name: 'session_add', methods: ['GET', 'POST'])]
    public function sessionAdd(
        Request $request,
        SessionRepository $sessionRepository,
        BikeRideService $bikeRideService,
        UserService $userService,
        BikeRide $bikeRide
    ): Response {
        $user = $this->getUser();

        $clusters = $bikeRide->getClusters();
        if ($clusters->isEmpty()) {
            $bikeRideService->createClusters($bikeRide);
            $this->entityManager->flush();
            $clusters = $bikeRide->getClusters();
        }
        $userSession = $sessionRepository->findByUserAndClusters($user, $clusters);
        $isAlreadyRegistered = ($userSession) ? true : false;

        $this->userPresenter->present($user);
        $isEndTesting = $this->userPresenter->viewModel()->isEndTesting();

        list($framers, $members) = $this->sessionService->getSessionsBytype($bikeRide);

        if (null === $userSession) {
            $userCluster = $this->sessionService->getCluster($bikeRide, $user, $clusters);

            $userSession = new Session();
            $userSession->setUser($user)
                ->setCluster($userCluster)
            ;
        }

        $form = $this->createForm(SessionEditType::class, $userSession, [
            'clusters' => $clusters,
            'bikeRide' => $bikeRide,
            'is_already_registered' => $isAlreadyRegistered,
            'is_end_testing' => $isEndTesting,
        ]);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $userSession = $form->getData();
            $user->addSession($userSession);

            $this->entityManager->persist($userSession);
            $this->entityManager->flush();
            $this->addFlash('success', 'Votre inscription a bien été prise en compte');

            $this->sessionService->checkEndTesting($user);

            return $this->redirectToRoute('user_bike_rides');
        }

        return $this->render('session/add.html.twig', [
            'form' => $form->createView(),
            'bikeRide' => $bikeRide,
            'framers' => $framers,
            'members' => $members,
            'is_already_registered' => $isAlreadyRegistered,
            'is_end_testing' => $isEndTesting,
        ]);
    }

    #[Route('/mon-compte/rando/disponibilte/{session}', name: 'session_availability_edit', methods: ['GET', 'POST'])]
    public function sessionAvailabilityEdit(
        Request $request,
        Session $session
    ) {
        $bikeRide = $session->getCluster()->getBikeRide();
        $form = $this->createForm(SessionAvailabilityType::class, $session);
        $form->handleRequest($request);

        list($framers, $members) = $this->sessionService->getSessionsBytype($bikeRide, $session->getUser());

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $session = $form->getData();

            $this->entityManager->flush();

            $this->addFlash('success', 'Votre disponiblité à bien été modifiée');

            return $this->redirectToRoute('user_bike_rides');
        }

        return $this->render('session/edit.html.twig', [
            'form' => $form->createView(),
            'bikeRide' => $bikeRide,
            'framers' => $framers,
            'members' => $members,
        ]);
    }

    #[Route('/mon-compte/rando/supprime/{session}', name: 'session_delete', methods: ['GET', 'POST'])]
    public function sessionDelete(
        FormFactoryInterface $formFactory,
        Request $request,
        Session $session
    ) {
        $form = $formFactory->create();
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $this->entityManager->remove($session);
            $this->entityManager->flush();

            $this->addFlash('success', 'Votre désinscription à bien été prise en compte');

            return $this->redirectToRoute('user_bike_rides');
        }

        return $this->render('session/delete.html.twig', [
            'form' => $form->createView(),
            'session' => $session,
        ]);
    }
}
