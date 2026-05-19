<?php

declare(strict_types=1);

namespace App\Mapper\Activity;

use App\Dto\ButtonDto;
use App\Dto\DropdownDto;
use App\Dto\DropdownItemDto;
use App\Dto\Enum\ColorVariant;
use App\Dto\HtmlAttributDto;
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

    public function mapToView(BikeRide $bikeRide): DropdownDto
    {
        $menuItems = [];
        if ($this->security->isGranted('ROLE_ADMIN')) {
            $menuItems[] = new ButtonDto(
                label: 'Modifier',
                url: $this->urlGenerator->generate('admin_bike_ride_edit', ['bikeRide' => $bikeRide->getId()]),
                icon: 'lucide:pencil',
                variant: ColorVariant::DROPDOWN,
            );
            if ($bikeRide->getStartAt() > new DateTimeImmutable()) {
                $menuItems[] = new ButtonDto(
                    label: 'Annuler',
                    url: $this->urlGenerator->generate('admin_bike_ride_delete', ['bikeRide' => $bikeRide->getId()]),
                    icon: 'lucide:delete',
                    variant: ColorVariant::DROPDOWN,
                    htmlAttributes: [
                        new HtmlAttributDto('data-turbo-frame', ButtonDto::MODAL_CONTENT),
                        new HtmlAttributDto('data-action', 'click->dropdown#close'),
                    ],
                );
            }
            $menuItems[] = new ButtonDto(
                label: 'Exporter la séance',
                url: $this->urlGenerator->generate('admin_bike_ride_export', ['bikeRide' => $bikeRide->getId()]),
                icon: 'lucide:file-down',
                variant: ColorVariant::DROPDOWN,
            );
        }
        if ($this->security->isGranted('SUMMARY_LIST')) {
            $menuItems[] = new ButtonDto(
                label: 'Actualités',
                url: $this->urlGenerator->generate('admin_summary_list', ['bikeRide' => $bikeRide->getId()]),
                icon: 'lucide:image',
                variant: ColorVariant::DROPDOWN,
            );
        }
        $actionItems = [];
        if ($bikeRide->getBikeRideType()->isPublic()) {
            $actionItems[] = new DropdownItemDto(
                label: 'Copier l\'url',
                icon: 'lucide:clipboard-copy',
                htmlAttributes: [
                    new HtmlAttributDto(
                        'data-clipboard-url-value',
                        $this->urlGenerator->generate(
                            'bike_ride_detail',
                            ['bikeRide' => $bikeRide->getId(), 'slug' => $bikeRide->getTitle()],
                            UrlGeneratorInterface::ABSOLUTE_URL
                        )
                    ),
                    new HtmlAttributDto('data-controller', 'clipboard'),
                    new HtmlAttributDto('data-action', 'click->dropdown#close')
                ]
            );
        }
                                                         
        return new DropdownDto(
            title: $bikeRide->__toString(),
            menuItems: $menuItems,
            actionItems: $actionItems,
        );
    }
}
