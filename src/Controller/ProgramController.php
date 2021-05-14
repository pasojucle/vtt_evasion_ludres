<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProgramController extends AbstractController
{
    /**
     * @Route("/program", name="program")
     */
    public function index(): Response
    {
        return $this->render('program/index.html.twig', [
            'controller_name' => 'ProgramController',
        ]);
    }
}
