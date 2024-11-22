<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Entity\BikeRide;
use App\Entity\Session;
use App\Entity\User;
use App\Repository\ContentRepository;
use App\Repository\RespondentRepository;
use App\Repository\SurveyResponseRepository;
use App\Service\CacheService;
use App\Service\MessageService;
use App\Service\SessionService;
use App\UseCase\Session\GetFormSession;
use App\UseCase\Session\SetSession;
use App\UseCase\Session\UnregistrableSessionMessage;
use App\UseCase\User\GetBikeRides;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class SessionController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CacheService $cacheService,
        private readonly SessionService $sessionService,
        private readonly GetFormSession $getFormSession,
        private readonly SetSession $setSession,
    ) {
    }


    #[Route('/mon-compte/programme', name: 'user_sessions', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function userBikeRides(
        UserDtoTransformer $userDtoTransformer,
        GetBikeRides $getBikeRides,
        ContentRepository $contentRepository
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('session/list.html.twig', [
            'user' => $userDtoTransformer->fromEntity($user),
            'sessions' => $getBikeRides->execute($user),
            'backgrounds' => $contentRepository->findOneByRoute('user_account')?->getBackgrounds(),
        ]);
    }

    #[Route('/mon-compte/rando/inscription/{bikeRide}', name: 'session_add', methods: ['GET', 'POST'])]
    public function sessionAdd(
        Request $request,
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

        $form = $this->getFormSession->toAdd($user, $bikeRide);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $this->setSession->add($form, $user, $bikeRide);

            $this->sessionService->checkEndTesting($user);

            return $this->redirectToRoute('user_sessions');
        }

        return $this->render('session/edit.html.twig', $this->getFormSession->params);
    }

    #[Route('/mon-compte/rando/disponibilte/{session}', name: 'session_availability_edit', methods: ['GET', 'POST'])]
    public function sessionAvailabilityEdit(
        Request $request,
        Session $session
    ) {
        $form = $this->getFormSession->toEdit($session);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $this->setSession->edit($form, $session);

            $this->addFlash('success', 'Votre disponibilité a bien été modifiée');
            return $this->redirectToRoute('user_sessions');
        }

        return $this->render('session/edit.html.twig', $this->getFormSession->params);
    }

    #[Route('/mon-compte/rando/supprime/{session}', name: 'session_delete', methods: ['GET', 'POST'])]
    public function sessionDelete(
        FormFactoryInterface $formFactory,
        Request $request,
        SurveyResponseRepository $surveyResponseRepository,
        RespondentRepository $respondentRepository,
        MessageService $messageService,
        Session $session
    ) {
        $bikeRide = $session->getCluster()->getBikeRide();
        if (!$bikeRide->registrationEnabled()) {
            return $this->render('session/can_unsubscribe.html.twig', [
                'session' => $session,
                'can_unsubcribe_message' => $messageService->getMessageByName('BIKE_RIDE_CAN_UNSUBSCRIBE_MESSAGE')
            ]);
        }
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

            return $this->redirectToRoute('user_sessions');
        }

        return $this->render('session/delete.html.twig', [
            'form' => $form->createView(),
            'session' => $session,
        ]);
    }
}
