<?php

declare(strict_types=1);

namespace App\Mapper\Participation;

use App\Dto\Enum\ColorVariant;
use App\Dto\View\BadgeView;
use App\Dto\View\DropdownView;
use App\Dto\View\HtmlAttributeView;
use App\Dto\View\LabelView;
use App\Dto\View\LinkView;
use App\Dto\View\ListItemView;
use App\Entity\BikeRide;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ParticipationMapper
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function mapToView(
        BikeRide $activity,
        int $total,
        array $params,
    ): ListItemView {
        return new ListItemView(
            id: sprintf('activity-%s', $activity->getId()),
            labels: [
                    new LabelView($activity->getStartAt()->format('d/m/y'), LabelView::TYPE_DATE),
                    new LabelView($activity->getTitle()),
                ],
            counter: new BadgeView(
                value: (string) $total,
                variant: ColorVariant::DEFAULT,
            ),
            dropdown: $this->dropDown($params),
            gridTemplateContent: 'grid-cols-[1fr_50px]',
            gridTemplateLabels: 'grid-cols-1 lg:grid-cols-[80px_1fr]',
        );
    }
    private function dropDown(array $params): DropdownView
    {
        return  new DropdownView(
            menuItems: [
                new LinkView(
                    label: 'Afficher les participants',
                    url: $this->urlGenerator->generate('admin_participation_activity_show', $params),
                    icon: 'lucide:pencil',
                    variant: ColorVariant::DROPDOWN,
                    htmlAttributes: [
                        new HtmlAttributeView('data-turbo-frame', LinkView::SHEET_CONTENT),
                        new HtmlAttributeView('data-action', 'click->dropdown#close'),
                    ],
                ),
            ]
        );
    }
}
