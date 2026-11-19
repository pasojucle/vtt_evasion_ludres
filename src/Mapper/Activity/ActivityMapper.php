<?php

declare(strict_types=1);

namespace App\Mapper\Activity;

use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\Size;
use App\Dto\View\BadgeView;
use App\Dto\View\LabelView;
use App\Dto\View\ListItemUrlView;
use App\Dto\View\ListItemView;
use App\Entity\BikeRide;
use App\Service\UrlContextService;
use DateTime;

class ActivityMapper
{
    public function __construct(
        private ActivityAdminDropdownMapper $activityAdminDropdownMapper,
        private UrlContextService $urlContextService,
    ) {
    }

    public function mapToView(
        BikeRide $entity,
        array $participantTotalByEntity,
        string $targetUrl,
    ): ListItemView {
        $isComplete = ($entity->getEndAt() ?? $entity->getStartAt()) < new DateTime();
        $participantsTotal = "0";
        if (array_key_exists($entity->getId(), $participantTotalByEntity)) {
            $participantsTotal = (string) $participantTotalByEntity[$entity->getId()][$isComplete ? 'present' : 'count'];
        }
        return new ListItemView(
            id: sprintf('activity-item-%s', $entity->getId()),
            labels: [
                    new LabelView($entity->getStartAt()->format('d/m/y'), LabelView::TYPE_DATE),
                    new LabelView($entity->getTitle()),
                ],
            indicators: $this->getIndicators($entity),
            status: $this->getStatus($entity, $isComplete),
            counter: new BadgeView(
                    $participantsTotal,
                    $isComplete ? ColorVariant::SUCCESS : ColorVariant::DEFAULT,
                ),
            dropdown: $this->activityAdminDropdownMapper->mapToView($entity, $targetUrl),
            isDeleted: $entity->isDeleted(),
            url: new ListItemUrlView(
                    $this->urlContextService->generateUrl(
                        "admin_cluster_list_activity",
                        ['bikeRide' => $entity->getId()],
                        $targetUrl
                    )
                ),
            gridTemplateContent: 'grid-cols-1 lg:grid-cols-[2fr_1fr]',
            gridTemplateLabels: 'grid-cols-[80px_auto]',
            gridTemplateBadges: 'grid-cols-[80px_auto_40px]',
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

        if (!$entity->getSummaries()->isEmpty()) {
            $indicators[] = new BadgeView(
                value: 'lucide:image',
                variant: ColorVariant::ACCENT,
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
