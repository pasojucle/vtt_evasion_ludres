<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BikeRideController extends AbstractController
{
    /**
     * @Route("/bike/ride", name="bike_ride")
     */
    public function index(): Response
    {
        return $this->render('bike_ride/index.html.twig', [
            'controller_name' => 'BikeRideController',
        ]);
    }
}
