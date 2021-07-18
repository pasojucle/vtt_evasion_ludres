<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Cluster;
use App\Entity\Session;
use App\Form\SessionAddType;
use App\Form\SessionType;
use App\Repository\ClusterRepository;
use App\Repository\SessionRepository;
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
        SessionRepository $sessionRepository
    ): Response
    {
        $user = $this->getUser();
        $clusters = $event->getClusters();
        $session = $sessionRepository->findByUserAndClusters($user, $clusters);

        if (null === $session) {
            $userCluster = null;
            if ($event->getType() === Event::TYPE_SCHOOL && null !== $user->getLevel()) {
                $clustersLevelAsUser = [];
                foreach($event->getClusters() as $cluster) {
                    if ($cluster->getLevel() === $user->getLevel()) {
                        $clustersLevelAsUser[] = $cluster;
                        if (count($cluster->getMemberSessions()) <= $cluster->getMaxUsers()) {
                            $userCluster = $cluster;
                        }
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

            $session = new Session();
            $session->setUser($user)
                ->setCluster($userCluster);
        }

        $form = $this->createForm(SessionAddType::class, $session, [
            'clusters' => $clusters,
        ]);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $session = $form->getData();

            $this->entityManager->persist($session);
            $this->entityManager->flush();
            $this->addFlash('success', 'Votre inscription a bien été prise en compte');

            return $this->redirectToRoute('user_account');
        }



        return $this->render('session/add.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
        ]);
    }
}
