<?php

declare(strict_types=1);

namespace App\Mapper\Dashboard;

use App\Dto\View\Dashboard\DashboardWidgetBikeRideView;
use App\Dto\View\Dashboard\DashboardWidgetListView;
use App\Dto\View\Dashboard\DashboardWidgetParticipationView;
use App\Dto\View\Dashboard\DashboardWidgetsView;
use App\Entity\Enum\OrderStatusEnum;
use App\Service\BikeRideService;
use App\Service\SeasonService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DashboardWidgetsMapper
{
    public function __construct(
        private BikeRideService $bikeRideService,
        private UrlGeneratorInterface $urlGenerator,
        private SeasonService $seasonService,
    ) {
    }

 
    public function mapToView(array $bikeRides): DashboardWidgetsView
    {
        return new DashboardWidgetsView(
            bikeRides: array_map(fn ($entity) => new DashboardWidgetBikeRideView(
                id: $entity->getId(),
                title: $this->bikeRideService->getShorTilte($entity->getTitle()),
                period: $this->bikeRideService->getPeriod($entity),
            ), $bikeRides),
            participations: [
                new DashboardWidgetParticipationView(
                    title: 'Participation école VTT',
                    isSchool: (int) true,
                ),

                new DashboardWidgetParticipationView(
                    title: 'Participation Adultes',
                    isSchool: (int) false,
                ),
            ],
            lists: [
                new DashboardWidgetListView(
                    title: 'Commandes',
                    id: 'dashboard-orders',
                    frameUrl: $this->urlGenerator->generate('admin_dashboard_orders'),
                    url: $this->urlGenerator->generate('admin_order_list', ['status' => OrderStatusEnum::ORDERED->value])
                ),
                new DashboardWidgetListView(
                    title: 'Annonces d\'occasion',
                    id: 'dashboard-second-hands',
                    frameUrl: $this->urlGenerator->generate('admin_dashboard_second_hands'),
                    url: $this->urlGenerator->generate('admin_second_hand_list'),
                ),
                new DashboardWidgetListView(
                    title: sprintf('Saison %s', $this->seasonService->getCurrentSeason()),
                    id: 'dashboard-season',
                    frameUrl: $this->urlGenerator->generate('admin_dashboard_saison_detail'),
                    url: $this->urlGenerator->generate('admin_user_list'),
                ),
            ]
        );
    }
}
