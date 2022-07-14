<?php

declare(strict_types=1);

namespace App\Controller;

use DateTime;
use App\Entity\User;
use App\Entity\Session;
use App\Entity\BikeRide;
use App\Form\SessionType;
use App\Entity\Respondent;
use App\Form\SessionEditType;
use App\Entity\SurveyResponse;
use App\Service\SessionService;
use App\Service\BikeRideService;
use App\ViewModel\UserPresenter;
use App\ViewModel\BikeRidePresenter;
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
        BikeRidePresenter $bikeRidePresenter,
        BikeRide $bikeRide
    ): Response {
        /** @var User $user */
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
        $bikeRidePresenter->present($bikeRide);
        $surveyResponses = null;
        if (null !== $bikeRide->getSurvey() && !$bikeRide->getSurvey()->getSurveyIssues()->isEmpty()) {
            $uuid = uniqid('', true);
            foreach ($bikeRide->getSurvey()->getSurveyIssues() as $issue) {
                $response = new SurveyResponse();
                $response->setSurveyIssue($issue)
                    ->setUuid($uuid)
                ;
                $surveyResponses[] = $response;
            }
        }
        $form = $this->createForm(SessionType::class, ['session' => $userSession, 'responses' =>['surveyResponses' => $surveyResponses]], [
            'clusters' => $clusters,
            'bikeRide' => $bikeRidePresenter->viewModel(),
            'is_already_registered' => $isAlreadyRegistered,
            'is_end_testing' => $isEndTesting,
        ]);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $user->addSession($data['session']);

            if (array_key_exists('responses', $data) && !empty($data['responses']['surveyResponses'])) {
                foreach ($data['responses']['surveyResponses'] as $response) {
                    if (!$bikeRide->getSurvey()->isAnonymous()) {
                        $response->setUser($user);
                    }
                    $this->entityManager->persist($response);
                }
                $now = new DateTime();

                $respondent = new Respondent();
                $respondent->setUser($user)
                    ->setSurvey($bikeRide->getSurvey())
                    ->setCreatedAt($now)
                ;
                $this->entityManager->persist($respondent);
            }

            $this->entityManager->persist($data['session']);
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
