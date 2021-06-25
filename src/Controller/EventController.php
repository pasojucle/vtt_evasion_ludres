<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EventController extends AbstractController
{
    /**
     * @Route("/admin/calendrier", name="admin_events")
     */
    public function adminList(): Response
    {
        return $this->render('event/list.html.twig', [
            'controller_name' => 'EventController',
        ]);
    }

    /**
     * @Route("/admin/sortie", name="admin_event_edit")
     */
    public function adminEdit(): Response
    {
        return $this->render('event/list.html.twig', [
            'controller_name' => 'EventController',
        ]);
    }
}
