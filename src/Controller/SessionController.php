<?php

namespace App\Controller;

use App\DataTransferObject\User;
use App\Entity\Event;
use App\Entity\Cluster;
use App\Entity\Session;
use App\Form\SessionAddType;
use App\Form\SessionType;
use App\Repository\ClusterRepository;
use App\Repository\SessionRepository;
use App\Repository\UserRepository;
use App\Service\EventService;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SessionController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private SessionInterface $session;

    public function __construct(EntityManagerInterface $entityManager, SessionInterface $session)
    {
        $this->entityManager = $entityManager;
        $this->session = $session;
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
        $form = $this->createForm(SessionType::class, $session);

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

        $members = [];
        $framers = [];
        $sessions = $sessionRepository->findByEvent($event);
 
        if (null !== $sessions) {
            foreach($sessions as $session) {
                if (null === $session->getAvailability()) {
                    $level = $session->getUser()->getLevel();
                    $levelId = (null !== $level) ? $level->getId() : 0;
                    $levelTitle = (null !== $level) ? $level->getTitle() : 'non renseigné';
                    $members[$levelId]['users'] = $session->getUser();
                    $members[$levelId]['title'] = $levelTitle;
                } else {
                    $framers[] = [
                        'user' => new User($session->getUser()),
                        'availability' => Session::AVAILABILITIES[$session->getAvailability()],
                    ];
                }
            }
        }

        

        if (null === $userSession) {
            $userCluster = null;
            if ($event->getType() === Event::TYPE_SCHOOL) {
                $clustersLevelAsUser = [];
                foreach($event->getClusters() as $cluster) {
                    if (null !== $cluster->getLevel() && $cluster->getLevel() === $user->getLevel()) {
                        $clustersLevelAsUser[] = $cluster;
                        if (count($cluster->getMemberSessions()) <= $cluster->getMaxUsers()) {
                            $userCluster = $cluster;
                        }
                    }
                    if (null !== $cluster->getRole() && $this->isGranted($cluster->getRole())) {
                        $userCluster = $cluster;
                    }
                }

                if (null === $userCluster) {
                    $cluster = new Cluster();
                    $count = count($clustersLevelAsUser) + 1;
                    $cluster->setTitle($user->getLevel()->getTitle().' '.$count)
                        ->setLevel($user->getLevel())
                        ->setEvent($event)
                        ->setMaxUsers(Cluster::SCHOOL_MAX_MEMEBERS);
                }
            }
            
            if (null === $userCluster && 1 === $clusters->count()) {
                $userCluster = $clusters->first();
            }

            $userSession = new Session();
            $userSession->setUser($user)
                ->setCluster($userCluster);
        }

        $form = $this->createForm(SessionAddType::class, $userSession, [
            'clusters' => $clusters,
            'event' => $event,
            'is_already_registered' => $isAlreadyRegistered,
        ]);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $userSession = $form->getData();
            
            $this->entityManager->persist($userSession);
            $this->entityManager->flush();
            $this->addFlash('success', 'Votre inscription a bien été prise en compte');

            return $this->redirectToRoute('user_account');
        }

        return $this->render('session/add.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
            'framers' => $framers,
            'members' => $members,
        ]);
    }
}
