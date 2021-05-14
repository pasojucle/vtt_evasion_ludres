<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LinkController extends AbstractController
{
    /**
     * @Route("/link", name="link")
     */
    public function index(): Response
    {
        return $this->render('link/index.html.twig', [
            'controller_name' => 'LinkController',
        ]);
    }
}
