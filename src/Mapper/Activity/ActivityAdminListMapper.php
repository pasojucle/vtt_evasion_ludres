<?php

declare(strict_types=1);

namespace App\Mapper\Activity;

use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\RoundedVariant;
use App\Dto\Enum\Size;
use App\Dto\Filter\ActivityFilter;
use App\Dto\View\BadgeView;
use App\Dto\View\DropdownView;
use App\Dto\View\HtmlAttributView;
use App\Dto\View\LabelView;
use App\Dto\View\LinkView;
use App\Dto\View\ListItemView;
use App\Dto\View\ListView;
use App\Entity\BikeRide;
use App\Mapper\DropdownSettingsMapper;
use App\Mapper\FilterChipsMapper;
use App\Mapper\PaginatorMapper;
use App\Service\Filter\FilterConfigInterface;
use App\Service\UrlContextService;
use DateTime;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ActivityAdminListMapper
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private DropdownSettingsMapper $dropdownSettingsMapper,
        private ActivityAdminDropdownMapper $activityAdminDropdownMapper,
        private FilterChipsMapper $filterChipsMapper,
        private PaginatorMapper $paginatorMapper,
        private UrlContextService $urlContextService,
    ) {
    }

    public function mapToView(
        Paginator $entities,
        array $participantTotalByEntity,
        string $route,
        int $currentPage,
        ActivityFilter $filter,
        FilterConfigInterface $filterConfig
    ): ListView {
        $referer = $this->urlContextService->generateTargetUrl($route, $filter->toQueryParams($currentPage));

        $items = [];
        /** @var BikeRide $entity */
        foreach ($entities as $entity) {
            $isComplete = ($entity->getEndAt() ?? $entity->getStartAt()) < new DateTime();
            $participantsTotal = "0";
            if (array_key_exists($entity->getId(), $participantTotalByEntity)) {
                $participantsTotal = (string) $participantTotalByEntity[$entity->getId()][$isComplete ? 'present' : 'count'];
            }
            $items[] = new ListItemView(
                labels: [
                    new LabelView($entity->getStartAt()->format('d/m/y')),
                    new LabelView($entity->getTitle()),
                ],
                indicators: $this->getIndicators($entity),
                status: $this->getStatus($entity, $isComplete),
                counter: new BadgeView(
                    $participantsTotal,
                    $isComplete ? ColorVariant::SUCCESS : ColorVariant::DEFAULT,
                ),
                dropdown: $this->activityAdminDropdownMapper->mapToView($entity, $referer),
                isDeleted: $entity->isDeleted(),
                url: $this->urlGenerator->generate("admin_cluster_list_activity", ['bikeRide' => $entity->getId()]),
                gridTemplateContent: 'grid-cols-1 lg:grid-cols-[2fr_1fr]',
                gridTemplateLabels: 'grid-cols-[80px_auto]',
                gridTemplateBadges: 'grid-cols-[80px_auto_40px]',
            );
        }

        return new ListView(
            name: 'activity',
            title: 'Programme des activités',
            description: 'Administration des activités : création, modification.',
            items: $items,
            settings: $this->settings($referer),
            paginator: $this->paginatorMapper->mapToView($entities, $route, $currentPage, $filter),
            advancedFilter: new LinkView(
                url: $this->urlGenerator->generate('admin_fiter_advanced', array_merge(['route' => $route], $filter->toQueryParams())),
                icon: 'lucide:settings-2',
                size: Size::ICON,
                htmlAttributes: [
                    new HtmlAttributView('data-turbo-frame', LinkView::SHEET_CONTENT),
                    new HtmlAttributView('data-action', 'click->dropdown#close')
                ],
            ),
            filterChipViews: $this->filterChipsMapper->mapToView($filter, $filterConfig->getRouteName(), $filterConfig->getAdvancedFields()),
            addItem: new LinkView(
                label: 'Ajouter une activité',
                url: $this->urlContextService->generateUrl('admin_bike_ride_add', [], $referer),
                icon: 'lucide:plus',
                variant: ColorVariant::DEFAULT,
            ),
        );
    }

    //     {% if bikeRide.bikeRideType.isRegistrable %}
    //     <a class="" href="{{ path('admin_cluster_list_activity', {'bikeRide' : bikeRide.id}) }}" title="Voir les participants">
    //         {{ bikeRide_content }}
    //     </a>
    // {% else %}
    //     <div class="list-item">{{ bikeRide_content }}</div>
    // {% endif %}
    // {% if is_granted('ROLE_ADMIN') or is_granted('SUMMARY_LIST') %}
    //     {% include 'components/_dropdown.html.twig' with {'dropdown': bikeRide.dropdown} %}
    // {% endif %}

    private function settings(string $referer): DropdownView
    {
        return $this->dropdownSettingsMapper->mapToView('BIKE_RIDE', $referer, RoundedVariant::ROUNDED, [
            new LinkView(
                label: 'Types de rando',
                url: $this->urlGenerator->generate('admin_bike_ride_type_list'),
                variant: ColorVariant::DROPDOWN,
            ),
            new LinkView(
                label: 'Indemnités',
                url: $this->urlGenerator->generate('admin_indemnity_list'),
                variant: ColorVariant::DROPDOWN,
            ),
        ]);
    }

    private function getIndicators(BikeRide $entity): array
    {
        $indicators = [];
        if (!$entity->getMembers()->isEmpty()) {
            $indicators[] = new BadgeView(
                value: 'lucide:users',
                variant: ColorVariant::ACCENT,
                size: Size::ICON
            );
        }
        if ($entity->getMaxAge() || $entity->getMinAge()) {
            $indicators[] = new BadgeView(
                value: 'lucide:cake',
                variant: ColorVariant::ACCENT,
                size: Size::ICON
            );
        }
        if (!$entity->registrationEnabled()) {
            $indicators[] = new BadgeView(
                value: 'lucide:lock',
                variant: ColorVariant::WARNING,
                size: Size::ICON
            );
        }
        if ($entity->isPrivate()) {
            $indicators[] = new BadgeView(
                value: 'lucide:eye-off',
                variant: ColorVariant::WARNING,
                size: Size::ICON
            );
        }

        return $indicators;
    }

    private function getStatus(BikeRide $entity, bool $isComplete): BadgeView
    {
        if ($entity->isDeleted()) {
            return new BadgeView('Supprimée', ColorVariant::DESTRUCTIVE);
        }

        return $isComplete
            ? new BadgeView('Terminée', ColorVariant::WARNING)
            : new BadgeView('A venir', ColorVariant::SUCCESS);
    }
}
