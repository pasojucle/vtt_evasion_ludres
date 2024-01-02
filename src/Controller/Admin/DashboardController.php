<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\DtoTransformer\BikeRideDtoTransformer;
use App\Dto\DtoTransformer\ClusterDtoTransformer;
use App\Entity\BikeRide;
use App\Entity\OrderHeader;
use App\Entity\SecondHand;
use App\Repository\BikeRideRepository;
use App\Repository\OrderHeaderRepository;
use App\Repository\SecondHandRepository;
use App\Repository\SessionRepository;
use App\Service\ParameterService;
use App\UseCase\User\GetCurrentSeasonUsers;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/dashboard', name: 'admin_dashboard')]
class DashboardController extends AbstractController
{
    #[Route('/', name:'', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function dashboard(SessionRepository $sessionRepository): Response
    {
        return $this->render('dashboard/index.html.twig');
    }

    #[Route('/nextSchoolBikeRides', name: '_next_school_bike_rides', methods: ['GET'], options:['expose' => true])]
    #[IsGranted('BIKE_RIDE_LIST')]
    public function nextSchoolBikeRide(
        BikeRideRepository $bikeRideRepository,
        BikeRideDtoTransformer $bikeRideDtoTransformer,
        ClusterDtoTransformer $clusterDtoTransformer,
    ): Response {
        $bikeRides = [];
        /** @var BikeRide $bikeRide */
        foreach ($bikeRideRepository->findNextSchoolBikeRides() as $bikeRide) {
            $bikeRides[] = [
                'bikeRide' => $bikeRideDtoTransformer->getHeaderFromEntity($bikeRide),
                'clusters' => $clusterDtoTransformer->fromEntities($bikeRide->getClusters()),
            ];
        }

        return $this->render('dashboard/next_bike_rides.html.twig', [
            'bike_rides' => $bikeRides,
        ]);
    }

    #[Route('/season/detail', name: '_saison_detail', methods: ['GET'], options:['expose' => true])]
    #[IsGranted('USER_SHARE')]
    public function seasonDetail(
        Request $request,
        GetCurrentSeasonUsers $getCurentSeasonUsers,
        ParameterService $parameterService,
    ): Response {
        $season = $request->getSession()->get('currentSeason');

        $parameters = [
            [
                'label' => 'Inscription séances d\'essai (Ecole Vtt)',
                'value' => $parameterService->getParameterByName('SCHOOL_TESTING_REGISTRATION')
            ],
            [
                'label' => 'Ré-inscription',
                'value' => $parameterService->getParameterByName('NEW_SEASON_RE_REGISTRATION_ENABLED')
            ],
        ];

        return $this->render('dashboard/badge.html.twig', [
            'title' => sprintf('Saison %s', $season),
            'data' => $getCurentSeasonUsers->execute(),
            'parameters' => $parameters,
            'link' => $this->generateUrl('admin_registrations'),
        ]);
    }

    #[Route('/orders', name: '_orders', methods: ['GET'], options:['expose' => true])]
    #[IsGranted('PRODUCT_LIST')]
    public function orders(Request $request, OrderHeaderRepository $orderHeaderRepository): Response
    {
        $filters = ['status' => OrderHeader::STATUS_ORDERED];
        $request->getSession()->set('admin_orders_filters', $filters);
        $ordersByType = [
            OrderHeader::STATUS[OrderHeader::STATUS_ORDERED] => [],
            OrderHeader::STATUS[OrderHeader::STATUS_VALIDED] => [],
        ];
        /** @var OrderHeader $order */
        foreach ($orderHeaderRepository->findOrdersQuery()->getQuery()->getResult() as $order) {
            $type = OrderHeader::STATUS[$order->getStatus()];
            if (in_array($type, array_keys($ordersByType))) {
                $ordersByType[$type][] = $order;
            }
        }
        ksort($ordersByType);

        return $this->render('dashboard/badge.html.twig', [
            'title' => 'Commandes',
            'data' => $ordersByType,
            'link' => $this->generateUrl('admin_orders', ['filtered' => true]),
        ]);
    }

    #[Route('/second/hands', name: '_second_hands', methods: ['GET'], options:['expose' => true])]
    #[IsGranted('SECOND_HAND_LIST')]
    public function secondHands(Request $request, SecondHandRepository $secondHandRepository): Response
    {
        $secondHandToValidate = 'Nouvelles annonces';
        $secondHangValid = 'Annonces validées';
        $secondHandsByType = [
            $secondHandToValidate => [],
            $secondHangValid => [],
        ];
        /** @var SecondHand $secondHand */
        foreach ($secondHandRepository->findAllNotDeleted()as $secondHand) {
            $type = ($secondHand->isValid()) ? $secondHandToValidate : $secondHangValid;
            $secondHandsByType[$type][] = $secondHand;
        }

        return $this->render('dashboard/badge.html.twig', [
            'title' => 'Annonces d\'occasion',
            'data' => $secondHandsByType,
            'link' => $this->generateUrl('admin_second_hand_list'),
        ]);
    }
}
