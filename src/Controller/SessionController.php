<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Session;
use App\Form\SessionType;
use App\Repository\ClusterRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SessionController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
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
     * @Route("/mon-compte/sortie/inscription/{event}", name="session_add")
     */
    public function sessionAdd(
        Request $request,
        Event $event
    ): Response
    {
        

        $referer = $request->headers->get('referer');
        
        return $this->render('user/add.session.modal.html.twig');
    }

}
