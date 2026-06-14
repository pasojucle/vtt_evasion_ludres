<?php

declare(strict_types=1);

namespace App\Mapper\Activity;

use App\Dto\View\ButtonView;
use App\Dto\View\DropdownView;
use App\Dto\View\DropdownItemView;
use App\Dto\Enum\ColorVariant;
use App\Dto\View\HtmlAttributView;
use App\Entity\BikeRide;
use DateTimeImmutable;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ActivityAdminDropdownMapper
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private Security $security,
    ) {
    }

    public function mapToView(BikeRide $bikeRide): DropdownView
    {
        $menuItems = [];
        if ($this->security->isGranted('ROLE_ADMIN')) {
            $menuItems[] = new ButtonView(
                label: 'Modifier',
                url: $this->urlGenerator->generate('admin_bike_ride_edit', ['bikeRide' => $bikeRide->getId()]),
                icon: 'lucide:pencil',
                variant: ColorVariant::DROPDOWN,
            );
            if ($bikeRide->getStartAt() > new DateTimeImmutable()) {
                $menuItems[] = new ButtonView(
                    label: 'Annuler',
                    url: $this->urlGenerator->generate('admin_bike_ride_delete', ['bikeRide' => $bikeRide->getId()]),
                    icon: 'lucide:delete',
                    variant: ColorVariant::DROPDOWN,
                    htmlAttributes: [
                        new HtmlAttributView('data-turbo-frame', ButtonView::MODAL_CONTENT),
                        new HtmlAttributView('data-action', 'click->dropdown#close'),
                    ],
                );
            }
            $menuItems[] = new ButtonView(
                label: 'Exporter la séance',
                url: $this->urlGenerator->generate('admin_bike_ride_export', ['bikeRide' => $bikeRide->getId()]),
                icon: 'lucide:file-down',
                variant: ColorVariant::DROPDOWN,
            );
        }
        if ($this->security->isGranted('SUMMARY_LIST')) {
            $menuItems[] = new ButtonView(
                label: 'Actualités',
                url: $this->urlGenerator->generate('admin_summary_list', ['bikeRide' => $bikeRide->getId()]),
                icon: 'lucide:image',
                variant: ColorVariant::DROPDOWN,
            );
        }
        $actionItems = [];
        if ($bikeRide->getBikeRideType()->isPublic()) {
            $actionItems[] = new DropdownItemView(
                label: 'Copier l\'url',
                icon: 'lucide:clipboard-copy',
                htmlAttributes: [
                    new HtmlAttributView(
                        'data-clipboard-url-value',
                        $this->urlGenerator->generate(
                            'bike_ride_detail',
                            ['bikeRide' => $bikeRide->getId(), 'slug' => $bikeRide->getTitle()],
                            UrlGeneratorInterface::ABSOLUTE_URL
                        )
                    ),
                    new HtmlAttributView('data-controller', 'clipboard'),
                    new HtmlAttributView('data-action', 'click->dropdown#close')
                ]
            );
        }
                                                         
        return new DropdownView(
            title: $bikeRide->__toString(),
            menuItems: $menuItems,
            actionItems: $actionItems,
        );
    }
}
