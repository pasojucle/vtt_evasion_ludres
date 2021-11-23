<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ParameterController extends AbstractController
{
    /**
     * @Route("/parameters", name="admin_group_parameters")
     */
    public function list(): Response
    {
        return $this->render('parameter/list.html.twig', [
            'controller_name' => 'ParameterController',
        ]);
    }
}
