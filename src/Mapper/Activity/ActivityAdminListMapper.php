<?php

declare(strict_types=1);

namespace App\Mapper\Activity;

use App\Dto\BadgeDto;
use App\Dto\ButtonDto;
use App\Dto\DropdownDto;
use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\DropdownVariant;
use App\Dto\Enum\Size;
use App\Dto\Filter\ActivityFilter;
use App\Dto\HtmlAttributDto;
use App\Dto\LabelDto;
use App\Dto\ListDto;
use App\Dto\ListItemDto;
use App\Entity\BikeRide;
use App\Mapper\DropdownSettingsMapper;
use App\Mapper\PaginatorMapper;
use DateTime;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ActivityAdminListMapper
{
    public function __construct(
        private DropdownSettingsMapper $dropdownSettingsMapper,
        private ActivityAdminDropdownMapper $activityAdminDropdownMapper,
        private UrlGeneratorInterface $urlGenerator,
        private PaginatorMapper $paginatorMapper,
    ) {
    }

    public function mapToView(Paginator $entities, array $participantTotalByEntity, string $route, int $currentPage, ActivityFilter $filter): ListDto
    {
        $items = [];
        /** @var BikeRide $entity */
        foreach ($entities as $entity) {
            $isComplete = ($entity->getEndAt() ?? $entity->getStartAt()) < new DateTime();
            $participantsTotal = "0";
            if (array_key_exists($entity->getId(), $participantTotalByEntity)) {
                $participantsTotal = (string) $participantTotalByEntity[$entity->getId()][$isComplete ? 'present' : 'count'];
            }
            $items[] = new ListItemDto(
                labels: [
                    new LabelDto($entity->getStartAt()->format('d/m/y')),
                    new LabelDto($entity->getTitle()),
                ],
                indicators: $this->getBadges($entity),
                status: $this->getStatus($entity, $isComplete),
                counter: new BadgeDto(
                    $participantsTotal,
                    $isComplete ? ColorVariant::SUCCESS : ColorVariant::DEFAULT,
                ),
                dropdown: $this->activityAdminDropdownMapper->mapToView($entity),
                url: $this->urlGenerator->generate("admin_bike_ride_cluster_show", ['bikeRide' => $entity->getId()]),
            );
        }

        return new ListDto(
            items: $items,
            settings: $this->settings(),
            paginator: $this->paginatorMapper->fromEntities($entities, $route, $currentPage, $filter),
            advancedFilter: new ButtonDto(
                url: $this->urlGenerator->generate('admin_fiter_advanced', array_merge(['route' => 'admin_bike_rides'], $filter->toQueryParams())),
                icon: 'lucide:settings-2',
                htmlAttributes: [
                    new HtmlAttributDto('data-turbo-frame', ButtonDto::SHEET_CONTENT),
                    new HtmlAttributDto('data-action', 'click->dropdown#close')
                ],
            ),
            addItem: new ButtonDto(
                label: 'Ajouter une activité',
                url: $this->urlGenerator->generate('admin_bike_ride_add'),
                icon: 'lucide:plus',
                variant: ColorVariant::DEFAULT,
            ),
            wiki: new ButtonDto(
                url: $this->urlGenerator->generate('wiki_show', ['directory' => 'boutique']),
                title: 'wiki',
                icon: 'lucide:circle-help',
                variant: ColorVariant::DEFAULT,
                htmlAttributes: [
                    new HtmlAttributDto('target', '_blank'),
                ],
            ),
        );
    }

    //     {% if bikeRide.bikeRideType.isRegistrable %}
    //     <a class="" href="{{ path('admin_bike_ride_cluster_show', {'bikeRide' : bikeRide.id}) }}" title="Voir les participants">
    //         {{ bikeRide_content }}
    //     </a>
    // {% else %}
    //     <div class="list-item">{{ bikeRide_content }}</div>
    // {% endif %}
    // {% if is_granted('ROLE_ADMIN') or is_granted('SUMMARY_LIST') %}
    //     {% include 'component/_dropdown.html.twig' with {'dropdown': bikeRide.dropdown} %}
    // {% endif %}

    private function settings(): DropdownDto
    {
        return $this->dropdownSettingsMapper->mapToView('BIKE_RIDE', DropdownVariant::ROUNDED, [
            new ButtonDto(
                label: 'Types de rando',
                url: $this->urlGenerator->generate('admin_bike_ride_types'),
                variant: ColorVariant::DROPDOWN,
            ),
            new ButtonDto(
                label: 'Indemnités',
                url: $this->urlGenerator->generate('admin_indemnity_list'),
                variant: ColorVariant::DROPDOWN,
            ),
        ]);
    }

    private function getBadges(BikeRide $entity): array
    {
        $badges = [];
        if (!$entity->getMembers()->isEmpty()) {
            $badges[] = new BadgeDto(
                value: 'lucide:users',
                variant: ColorVariant::ACCENT,
                size: Size::ICON
            );
        }
        if ($entity->getMaxAge() || $entity->getMinAge()) {
            $badges[] = new BadgeDto(
                value: 'lucide:cake',
                variant: ColorVariant::ACCENT,
                size: Size::ICON
            );
        }
        if (!$entity->registrationEnabled()) {
            $badges[] = new BadgeDto(
                value: 'lucide:lock',
                variant: ColorVariant::WARNING,
                size: Size::ICON
            );
        }
        if ($entity->isPrivate()) {
            $badges[] = new BadgeDto(
                value: 'lucide:eye-off',
                variant: ColorVariant::WARNING,
                size: Size::ICON
            );
        }

        return $badges;
    }

    private function getStatus(BikeRide $entity, bool $isComplete): BadgeDto
    {
        if ($entity->isDeleted()) {
            return new BadgeDto('Supprimée', ColorVariant::DESTRUCTIVE);
        }

        return $isComplete
            ? new BadgeDto('Terminée', ColorVariant::WARNING)
            : new BadgeDto('A venir', ColorVariant::SUCCESS);
    }
}
