<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\BikeRide;
use App\State\Dashboard\Provider\DashboardBikeRideProvider;
use App\State\Dashboard\Provider\DashboardCronTabProvider;
use App\State\Dashboard\Provider\DashboardOrderProvider;
use App\State\Dashboard\Provider\DashboardSeasonProvider;
use App\State\Dashboard\Provider\DashboardSecondHandProvider;
use App\State\Dashboard\Provider\DashboardWidgetsProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/dashboard', name: 'admin_dashboard')]
class DashboardController extends AbstractController
{
    #[Route('/', name:'', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function dashboard(
        DashboardWidgetsProvider $provider,
    ): Response {
        return $this->render('dashboard/index.html.twig', [
            'widgets' => $provider->getWidgets(),
        ]);
    }

    #[Route('/bikeRide/{bikeRide}', name: '_bike_ride', methods: ['GET'])]
    #[IsGranted('BIKE_RIDE_LIST')]
    public function nextSchoolBikeRide(
        DashboardBikeRideProvider $provider,
        BikeRide $bikeRide,
    ): Response {
        return $this->render('dashboard/bike_rides.html.twig', [
            'bikeRide' => $provider->getItem($bikeRide),
        ]);
    }

    #[Route('/season/detail', name: '_saison_detail', methods: ['GET'], options:['expose' => true])]
    #[IsGranted('USER_SHARE')]
    public function seasonDetail(
        DashboardSeasonProvider $provider,
    ): Response {
        return $this->render('dashboard/list.html.twig', [
            'view' => $provider->getCollection(),
        ]);
    }

    #[Route('/orders', name: '_orders', methods: ['GET'], options:['expose' => true])]
    #[IsGranted('PRODUCT_LIST')]
    public function orders(
        DashboardOrderProvider $provider
    ): Response {
        return $this->render('dashboard/list.html.twig', [
            'view' => $provider->getCollection(),
        ]);
    }

    #[Route('/second/hands', name: '_second_hands', methods: ['GET'], options:['expose' => true])]
    #[IsGranted('SECOND_HAND_LIST')]
    public function secondHands(
        DashboardSecondHandProvider $provider,
    ): Response {
        return $this->render('dashboard/list.html.twig', [
            'view' => $provider->getCollection(),
        ]);
    }

    #[Route('/crontab', name: '_crontab', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function crontab(
        DashboardCronTabProvider $provider
    ): Response {
        return $this->render('dashboard/crontab.html.twig', [
            'cronTab' => $provider->getItem(),
        ]);
    }
}
