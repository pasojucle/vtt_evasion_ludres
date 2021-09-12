<?php

namespace App\Controller;

use App\DataTransferObject\User;
use App\Entity\Event;
use App\Entity\Cluster;
use App\Entity\Session;
use App\Service\UserService;
use App\Form\SessionEditType;
use App\Service\EventService;
use App\Form\Admin\SessionType;
use App\Form\SessionSwitchType;
use App\Service\SessionService;
use App\Repository\UserRepository;
use App\Form\SessionAvailabilityType;
use App\Repository\ClusterRepository;
use App\Repository\SessionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;

class SessionController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private SessionInterface $session;
    private SessionService $sessionService;

    public function __construct(EntityManagerInterface $entityManager, SessionInterface $session, SessionService $sessionService)
    {
        $this->entityManager = $entityManager;
        $this->session = $session;
        $this->sessionService = $sessionService;
    }
    /**
     * @Route("/admin/seance/{session}", name="admin_session_present")
     */
    public function adminPresent(
        Session $session
    ): Response
    {
        $isPresent = !$session->isPresent();

        $session->setIsPresent($isPresent);
        $this->entityManager->flush();

        return $this->redirectToRoute('admin_event_cluster_show', ['event' => $session->getCluster()->getEvent()->getId()]);
    }

    /**
     * @Route("/admin/groupe/change/{session}", name="admin_event_switch_cluster")
     */
    public function adminClusterSwitch(
        ClusterRepository $clusterRepository,
        Request $request,
        Session $session
    ): Response
    {

        $event = $session->getCluster()->getEvent();
        $form = $this->createForm(SessionSwitchType::class, $session);

        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $session = $form->getData();
            $this->entityManager->flush();

            return $this->redirectToRoute('admin_event_cluster_show', ['event' => $event->getId()]);
        }
        
        return $this->render('session/switch.html.twig', [
            'event' => $event,
            'session' => $session,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/mon-compte/rando/inscription/{event}",
     * name="session_add")
     */
    public function sessionAdd(
        Request $request,
        Event $event,
        SessionRepository $sessionRepository,
        UserRepository $userRepository,
        EventService $eventService,
        UserService $userService
    ): Response
    {
        $user = $this->getUser();

        $clusters = $event->getClusters();
        if ($clusters->isEmpty()) {
            $eventService->createClusters($event);
            $this->entityManager->flush();
            $clusters = $event->getClusters();
        }
        $userSession = $sessionRepository->findByUserAndClusters($user, $clusters);
        $isAlreadyRegistered = ($userSession) ? true : false;

        $domaineUser = $userService->convertToUser($user);
        $isEndTesting = $domaineUser->isEndTesting();

        list($framers, $members) = $this->sessionService->getSessionsBytype($event);

        if (null === $userSession) {
            $userCluster = $this->sessionService->getCluster($event, $user, $clusters);

            $userSession = new Session();
            $userSession->setUser($user)
                ->setCluster($userCluster);
        }

        $form = $this->createForm(SessionEditType::class, $userSession, [
            'clusters' => $clusters,
            'event' => $event,
            'is_already_registered' => $isAlreadyRegistered,
            'is_end_testing' => $isEndTesting,
        ]);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $userSession = $form->getData();
            
            $this->entityManager->persist($userSession);
            $this->entityManager->flush();
            $this->addFlash('success', 'Votre inscription a bien été prise en compte');

            $this->sessionService->checkEndTesting($user);

            return $this->redirectToRoute('user_account');
        }

        return $this->render('session/add.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
            'framers' => $framers,
            'members' => $members,
            'is_already_registered' => $isAlreadyRegistered,
            'is_end_testing' => $isEndTesting,
        ]);
    }


    /**
     * @Route("/admin/rando/inscription/{event}",
     * name="admin_session_add")
     */
    public function adminSessionAdd(
        Request $request,
        SessionRepository $sessionRepository,
        Event $event
    ): Response
    {
        $clusters = $event->getClusters();

        $form = $this->createForm(SessionType::class);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $userSession = $form->getData();
            $user = $userSession->getUser();
            if ($sessionRepository->findByUserAndClusters($user, $clusters)) {
                $form->addError(new FormError('Cet adhérent est déjà inscrit'));
            }
    
            if ($form->isValid()) {
                $userCluster = $this->sessionService->getCluster($event, $user, $clusters);
                $userSession->setCluster($userCluster);

                $this->entityManager->persist($userSession);
                $this->entityManager->flush();
                $this->addFlash('success', 'Le participant à bien été inscrit');

                $this->sessionService->checkEndTesting($user);

                return $this->redirectToRoute('admin_event_cluster_show', ['event' => $event->getId()]);
            }
            
        }

        return $this->render('session/admin/add.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
        ]);
    }

    /**
     * @Route("/mon-compte/rando/disponibilte/{session}",
     * name="session_availability_edit")
     */
    public function sessionAvailabilityEdit(
        Request $request,
        Session $session
    )
    {
        $event = $session->getCluster()->getEvent();
        $form = $this->createForm(SessionAvailabilityType::class, $session);
        $form->handleRequest($request);

        list($framers, $members) = $this->sessionService->getSessionsBytype($event, $session->getUser());

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $session = $form->getData();

            $this->entityManager->flush();

            $this->addFlash('success', 'Votre disponiblité à bien été modifiée');

            return $this->redirectToRoute('user_account_bike_rides');
        }

        return $this->render('session/edit.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
            'framers' => $framers,
            'members' => $members,
        ]);
    }

        
    /**
     * @Route("/mon-compte/rando/supprime/{session}",
     * name="session_delete")
     */
    public function sessionDelete(
        FormFactoryInterface $formFactory,
        Request $request,
        Session $session
    )
    {
        $form = $formFactory->create();
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $this->entityManager->remove($session);
            $this->entityManager->flush();

            $this->addFlash('success', 'Votre désinscrition à bien été prise en compte');

            return $this->redirectToRoute('user_account_bike_rides');
        }

        return $this->render('session/delete.html.twig', [
            'form' => $form->createView(),
            'session' => $session,
        ]);
    }
}
