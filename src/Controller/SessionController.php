<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\BikeRide;
use App\Entity\Session;
use App\Entity\User;
use App\Form\SessionAvailabilityType;
use App\Service\SessionService;
use App\UseCase\Session\EditSession;
use App\UseCase\Session\GetFormSession;
use App\ViewModel\BikeRidePresenter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
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

    #[Route('/mon-compte/rando/inscription/{bikeRide}', name: 'session_add', methods: ['GET', 'POST'])]
    public function sessionAdd(
        Request $request,
        GetFormSession $getFormSession,
        EditSession $editSession,
        BikeRide $bikeRide
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        $form = $getFormSession->execute($user, $bikeRide);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $editSession->execute($form, $user, $bikeRide);

            $this->addFlash('success', 'Votre inscription a bien été prise en compte');

            $this->sessionService->checkEndTesting($user);

            return $this->redirectToRoute('user_bike_rides');
        }

        return $this->render('session/add.html.twig', $getFormSession->params);
    }

    #[Route('/mon-compte/rando/disponibilte/{session}', name: 'session_availability_edit', methods: ['GET', 'POST'])]
    public function sessionAvailabilityEdit(
        Request $request,
        BikeRidePresenter $bikeRidePresenter,
        Session $session
    ) {
        $bikeRide = $session->getCluster()->getBikeRide();
        $form = $this->createForm(SessionAvailabilityType::class, $session);
        $form->handleRequest($request);

        list($framers, $members) = $this->sessionService->getSessionsBytype($bikeRide, $session->getUser());

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $session = $form->getData();

            $this->entityManager->flush();

            $this->addFlash('success', 'Votre disponibilité a bien été modifiée');

            return $this->redirectToRoute('user_bike_rides');
        }

        $bikeRidePresenter->present($bikeRide);
        return $this->render('session/edit.html.twig', [
            'form' => $form->createView(),
            'bikeRide' => $bikeRidePresenter->viewModel(),
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
