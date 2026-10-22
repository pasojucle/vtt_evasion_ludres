<?php

declare(strict_types=1);

namespace App\Mapper\ActivityType;

use App\Dto\Enum\ColorVariant;
use App\Dto\View\BadgeView;
use App\Dto\View\DropdownView;
use App\Dto\View\HtmlAttributeView;
use App\Dto\View\LabelView;
use App\Dto\View\LinkView;
use App\Dto\View\ListItemView;
use App\Entity\BikeRideType;
use App\Service\UrlContextService;

class ActivityTypeMapper
{
    public function __construct(
        private UrlContextService $urlContextService,
    ) {
    }

    public function mapToView(
        BikeRideType $bikeRideType,
        string $targetUrl,
    ): ListItemView {
        return new ListItemView(
            id: sprintf('activity-type-%s', $bikeRideType->getId()),
            labels: [
                new LabelView($bikeRideType->getName()),
            ],
            status: $this->getStatus($bikeRideType),
            dropdown: $this->dropDown($bikeRideType, $targetUrl),
            isDeleted: $bikeRideType->isDeleted(),
            gridTemplateContent: 'grid-cols-1 grid-cols-[1fr_100px]'
        );
    }

    private function dropDown(BikeRideType $bikeRideType, string $targetUrl): DropdownView
    {
        if ($bikeRideType->isDeleted()) {
            return new DropdownView(
                menuItems: [
                    new LinkView(
                        label: 'Restaurer',
                        url: $this->urlContextService->generateUrl('admin_bike_ride_type_restore', [
                            'bikeRideType' => $bikeRideType->getId()
                            ], $targetUrl),
                        icon: 'lucide:archive-restore',
                        variant: ColorVariant::DROPDOWN,
                    ),
                ]
            );
        }

        return  new DropdownView(
            menuItems: [
                new LinkView(
                    label: 'Modifier',
                    url: $this->urlContextService->generateUrl('admin_bike_ride_type_edit', [
                        'bikeRideType' => $bikeRideType->getId()
                        ], $targetUrl),
                    icon: 'lucide:pencil',
                    variant: ColorVariant::DROPDOWN,
                    htmlAttributes: [
                        new HtmlAttributeView('data-turbo-frame', LinkView::SHEET_CONTENT),
                        new HtmlAttributeView('data-action', 'click->dropdown#close')
                    ],
                ),
                new LinkView(
                    label: 'Supprimer',
                    url: $this->urlContextService->generateUrl('admin_bike_ride_type_delete', [
                        'bikeRideType' => $bikeRideType->getId()
                        ], $targetUrl),
                    icon: 'lucide:delete',
                    variant: ColorVariant::DROPDOWN,
                    htmlAttributes: [
                        new HtmlAttributeView('data-turbo-frame', LinkView::MODAL_CONTENT),
                        new HtmlAttributeView('data-action', 'click->dropdown#close'),
                    ],
                ),
            ]
        );
    }

    private function getStatus(BikeRideType $bikeRideType): ?BadgeView
    {
        if ($bikeRideType->isDeleted()) {
            return new BadgeView('Supprimée', ColorVariant::DESTRUCTIVE);
        }

        return null;
    }
}
