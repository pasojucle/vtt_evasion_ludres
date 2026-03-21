<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\DtoTransformer\BikeRideDtoTransformer;
use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Entity\BikeRide;
use App\Entity\Guest;
use App\Entity\Member;
use App\Entity\User;
use App\Entity\Session;
use App\Form\SessionGuestAddType;
use App\Repository\ContentRepository;
use App\Service\MessageService;
use App\Service\SessionService;
use App\UseCase\Session\AddGuestSession;
use App\UseCase\Session\GetFormSession;
use App\UseCase\Session\SetSession;
use App\UseCase\Session\UnregistrableSessionMessage;
use App\UseCase\User\GetBikeRides;
use Error;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class SessionController extends AbstractController
{
    public function __construct(
        private readonly SessionService $sessionService,
        private readonly GetFormSession $getFormSession,
        private readonly SetSession $setSession,
        private readonly AddGuestSession $addGuestSession,

    ) {
    }


    #[Route('/mon-compte/programme', name: 'user_sessions', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function userBikeRides(
        UserDtoTransformer $userDtoTransformer,
        GetBikeRides $getBikeRides,
        ContentRepository $contentRepository
    ): Response {
        /** @var Member $member */
        $member = $this->getUser();

        return $this->render('session/list.html.twig', [
            'user' => $userDtoTransformer->fromEntity($member),
            'sessions' => $getBikeRides->execute($member),
            'backgrounds' => $contentRepository->findOneByRoute('user_account')?->getBackgrounds(),
        ]);
    }

    #[Route('/mon-compte/rando/inscription/{bikeRide}', name: 'session_add', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function sessionAdd(
        Request $request,
        UnregistrableSessionMessage $unregistrableSessionMessage,
        BikeRide $bikeRide
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');
        /** @var Member $member */
        $member = $this->getUser();

        $unregistrable = $unregistrableSessionMessage->execute($member, $bikeRide);
        if (null !== $unregistrable) {
            return $this->render('session/unregistrable.html.twig', [
                'bikeRide' => $bikeRide,
                'unregistrable' => $unregistrable,
            ]);
        }

        $form = $this->getFormSession->toAdd($member, $bikeRide);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $this->setSession->add($form, $member, $bikeRide);

            $this->sessionService->checkEndTesting($member);

            return $this->redirectToRoute('user_sessions');
        }

        return $this->render('session/edit.html.twig', $this->getFormSession->params);
    }

    #[Route('/mon-compte/rando/disponibilte/{session}', name: 'session_availability_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
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
    #[IsGranted('ROLE_USER')]
    public function sessionDelete(
        FormFactoryInterface $formFactory,
        Request $request,
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
            $this->setSession->delete($session);

            $this->addFlash('success', 'Votre désinscription à bien été prise en compte');

            return $this->redirectToRoute('user_sessions');
        }

        return $this->render('session/delete.html.twig', [
            'form' => $form->createView(),
            'session' => $session,
        ]);
    }

    #[Route('/programe/inscription/close/{bikeRide}', name: 'registration_closed', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function registrationClosed(
        BikeRideDtoTransformer $bikeRideDtoTransformer,
        MessageService $messageService,
        BikeRide $bikeRide
    ) {
        return $this->render('session/registration_closed.modal.html.twig', [
            'bike_ride' => $bikeRideDtoTransformer->getHeaderFromEntity($bikeRide),
            'message' => $bikeRide->getRegistrationClosedMessage() ?? $messageService->getMessageByName('REGISTRATION_CLOSED_DEFAULT_MESSAGE')
        ]);
    }

    #[Route('/club/rando/inscription/{bikeRide}', name: 'session_guest_add', methods: ['GET', 'POST'])]
    public function guestAdd(
        Request $request,
        BikeRideDtoTransformer $bikeRideDtoTransformer,
        BikeRide $bikeRide,
    ) {
        $response = new Response("OK", Response::HTTP_OK);
        /** @var User $guest */
        $guest = $this->getUser();
        if (!$guest instanceof Guest) {
            throw new AccessDeniedException();
        }
        if (null !== $existingSession = $this->addGuestSession->getExistingSession($guest, $bikeRide)) {
            return $this->redirectToRoute('session_guest_participation', ['session' => $existingSession->getId()]);
        }
        $form = $this->createForm(SessionGuestAddType::class, $this->addGuestSession->getNewSession($guest), [
            'clusters' => $bikeRide->getClusters(),
        ]);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted()) {
            if ($form->isValid()) {
                $session = $this->addGuestSession->setSession($form);

                return $this->redirectToRoute('session_guest_participation', ['session' => $session->getId()]);
            }
            $response = new Response(null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->render('session/guest_add.html.twig', [
            'bikeRide' =>  $bikeRideDtoTransformer->fromEntity($bikeRide),
            'form' => $form->createView(),
            'publicRegistrationRate' => $this->addGuestSession->getPublicRegistrationRate(),
        ], $response);
    }

    #[Route('/club/rando/participation/{session}', name: 'session_guest_participation', methods: ['GET'])]
    public function guestParticipation(
        BikeRideDtoTransformer $bikeRideDtoTransformer,
        Session $session,
    ) {
        /** @var User $guest */
        $guest = $this->getUser();
        if ($guest !== $session->getUser()) {
            throw new AccessDeniedException();
        }
        $amount = $this->addGuestSession->getAmount($session);
        if (false === $amount) {
            throw new Error('Un problème est survenu pendant l\'inscription');
        }
        if (0 < $amount) {
            // TODO redirger vers le paiement
        }

        return $this->render('session/guest_participation.html.twig', [
            'bikeRide' =>  $bikeRideDtoTransformer->fromEntity($session->getCluster()->getBikeRide()),
            'amount' => 0 < $amount ? sprintf('%s €', $amount / 100) : 'Gratuit',
            'email' => $guest->getContactEmail(),
            'session' => $session,
            //TODO Ajouter les infos de transaction
        ]);
    }
}
