<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\DtoTransformer\BikeRideDtoTransformer;
use App\Entity\BikeRide;
use App\Entity\Session;
use App\Entity\User;
use App\Form\SessionAvailabilityType;
use App\Repository\RespondentRepository;
use App\Repository\SurveyResponseRepository;
use App\Service\CacheService;
use App\Service\SessionService;
use App\UseCase\Session\AddSession;
use App\UseCase\Session\ConfirmationSession;
use App\UseCase\Session\GetFormSession;
use App\UseCase\Session\UnregistrableSessionMessage;
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
        private CacheService $cacheService,
        private SessionService $sessionService
    ) {
    }

    #[Route('/mon-compte/rando/inscription/{bikeRide}', name: 'session_add', methods: ['GET', 'POST'])]
    public function sessionAdd(
        Request $request,
        GetFormSession $getFormSession,
        AddSession $addSession,
        UnregistrableSessionMessage $unregistrableSessionMessage,
        BikeRide $bikeRide
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');
        /** @var User $user */
        $user = $this->getUser();

        $unregistrableMessage = $unregistrableSessionMessage->execute($user, $bikeRide);
        if (null !== $unregistrableMessage) {
            return $this->render('session/unregistrable.html.twig', [
                'bikeRide' => $bikeRide,
                'unregistrableMessage' => $unregistrableMessage,
            ]);
        }

        $form = $getFormSession->execute($user, $bikeRide);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $addSession->execute($form, $user, $bikeRide);

            $this->sessionService->checkEndTesting($user);

            return $this->redirectToRoute('user_bike_rides');
        }

        return $this->render('session/add.html.twig', $getFormSession->params);
    }

    #[Route('/mon-compte/rando/disponibilte/{session}', name: 'session_availability_edit', methods: ['GET', 'POST'])]
    
    public function sessionAvailabilityEdit(
        Request $request,
        BikeRideDtoTransformer $bikeRideDtoTransformer,
        ConfirmationSession $confirmationSession,
        Session $session
    ) {
        $bikeRide = $session->getCluster()->getBikeRide();
        $form = $this->createForm(SessionAvailabilityType::class, $session);
        $form->handleRequest($request);

        $sessions = $this->sessionService->getSessionsBytype($bikeRide, $session->getUser());

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $session = $form->getData();

            $this->entityManager->flush();
            $this->cacheService->deleteCacheIndex($session->getCluster());
            $this->addFlash('success', 'Votre disponibilité a bien été modifiée');
            $confirmationSession->execute($session);

            return $this->redirectToRoute('user_bike_rides');
        }

        return $this->render('session/edit.html.twig', [
            'form' => $form->createView(),
            'bikeRide' => $bikeRideDtoTransformer->fromEntity($bikeRide),
            'sessions' => $sessions,
        ]);
    }

    #[Route('/mon-compte/rando/supprime/{session}', name: 'session_delete', methods: ['GET', 'POST'])]
    public function sessionDelete(
        FormFactoryInterface $formFactory,
        Request $request,
        SurveyResponseRepository $surveyResponseRepository,
        RespondentRepository $respondentRepository,
        Session $session
    ) {
        $form = $formFactory->create();
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            if ($survey = $session->getCluster()->getBikeRide()->getSurvey()) {
                $surveyResponseRepository->deleteResponsesByUserAndSurvey($session->getUser(), $survey);
                $respondentRepository->deleteResponsesByUserAndSurvey($session->getUser(), $survey);
            }
            $this->cacheService->deleteCacheIndex($session->getCluster());
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
