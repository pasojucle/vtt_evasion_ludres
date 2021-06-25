<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SessionController extends AbstractController
{
    /**
     * @Route("/admin/seances", name="admin_sessions")
     */
    public function adminList(): Response
    {

        return $this->render('session/list.html.twig', [
            'controller_name' => 'SessionController',
        ]);
    }

    /**
     * @Route("/admin/seance", name="admin_session_edit")
     */
    public function adminEdit(): Response
    {
        
        return $this->render('session/edit.html.twig', [
            'controller_name' => 'SessionController',
        ]);
    }
}
