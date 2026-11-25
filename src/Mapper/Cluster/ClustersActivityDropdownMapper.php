<?php

declare(strict_types=1);

namespace App\Mapper\Cluster;

use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\DropdownVariant;
use App\Dto\Enum\RoundedVariant;
use App\Dto\View\DropdownView;
use App\Dto\View\LinkView;
use App\Entity\BikeRide;
use App\Service\UrlContextService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ClustersActivityDropdownMapper
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private UrlContextService $urlContextService,
        private Security $security,
    ) {
    }

    public function mapToView(BikeRide $bikeRide, string $fallback): DropdownView
    {
        $menuItems = [];

        if ($this->security->isGranted('SUMMARY_LIST')) {
            $menuItems[] = new LinkView(
                label: 'Actualités',
                url: $this->urlContextService->generateUrl('admin_summary_list', ['bikeRide' => $bikeRide->getId()], $fallback),
                icon: 'lucide:image',
                variant: ColorVariant::DROPDOWN,
            );
        }

        $isAdmin = $this->security->isGranted('ROLE_ADMIN');
        if ($isAdmin) {
            ;
            $menuItems[] = new LinkView(
                label: 'Exporter la séance',
                url: $this->urlGenerator->generate('admin_bike_ride_export', ['bikeRide' => $bikeRide->getId()]),
                icon: 'lucide:file-down',
                variant: ColorVariant::DROPDOWN,
            );
        }
                                                         
        return new DropdownView(
            variant: DropdownVariant::BUTTON,
            rounded: RoundedVariant::ROUNDED,
            menuItems: $menuItems,
        );
    }
}
